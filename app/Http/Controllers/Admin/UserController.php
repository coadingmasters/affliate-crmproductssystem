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
     * Create a customer account and hand the credentials back to be shared.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->only(['name', 'email', 'password']),
            // Accounts created here are always customers.
            'role' => 'user',
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Account created for '.$user->name.'.')
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
     * Update an account. Admin accounts accept a password change only.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            $user->update(['password' => $request->validated('password')]);

            return redirect()
                ->route('admin.users.index')
                ->with('status', 'Admin password updated.')
                ->with('credentials', [
                    'email' => $user->email,
                    'password' => $request->validated('password'),
                ]);
        }

        $attributes = $request->safe()->only(['name', 'email']);

        // Blank password means "leave it alone".
        if ($request->filled('password')) {
            $attributes['password'] = $request->validated('password');
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
     * Delete a customer account. Admin accounts can never be deleted.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot be deleted.');
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
