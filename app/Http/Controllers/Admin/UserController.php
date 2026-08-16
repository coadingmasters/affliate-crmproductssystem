<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List every account.
     */
    public function index(Request $request): View
    {
        $role = $request->query('role');

        if (! in_array($role, ['admin', 'user'], true)) {
            $role = 'all';
        }

        $users = User::withCount('orders')
            ->when($role !== 'all', fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'role' => $role,
        ]);
    }

    /**
     * Show the create form.
     */
    public function create(): View
    {
        return view('admin.users.create', [
            'user' => new User(['role' => 'user']),
            'suggestedPassword' => $this->suggestPassword(),
        ]);
    }

    /**
     * Create an account and hand the credentials back so they can be shared.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Account created for '.$user->name.'.')
            // Shown once so the admin can pass the details to the user.
            ->with('credentials', [
                'email' => $user->email,
                'password' => $request->validated('password'),
            ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'suggestedPassword' => $this->suggestPassword(),
        ]);
    }

    /**
     * Update an account, optionally resetting its password.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->safe()->only(['name', 'email', 'role']);

        // Blank password means "leave it alone".
        if ($request->filled('password')) {
            $attributes['password'] = $request->validated('password');
        }

        // Never let an admin strip their own admin rights and lock themselves out.
        if ($user->is($request->user()) && $attributes['role'] !== 'admin') {
            return back()
                ->withInput()
                ->with('error', 'You cannot remove your own admin access.');
        }

        $user->update($attributes);

        $redirect = redirect()
            ->route('admin.users.index')
            ->with('status', $user->name.' updated.');

        if ($request->filled('password')) {
            $redirect->with('credentials', [
                'email' => $user->email,
                'password' => $request->validated('password'),
            ]);
        }

        return $redirect;
    }

    /**
     * Delete an account.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete the account you are signed in with.');
        }

        $name = $user->name;

        // Orders keep their history; the link is nulled by the foreign key.
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', $name.' was deleted. Their past orders were kept.');
    }

    /**
     * A reasonable password the admin can hand over as-is.
     */
    private function suggestPassword(): string
    {
        $words = ['Med', 'Alert', 'Care', 'Safe', 'Guard', 'Vital'];

        return $words[array_rand($words)].random_int(1000, 9999).'x';
    }
}
