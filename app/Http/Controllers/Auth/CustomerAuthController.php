<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CustomerLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    /**
     * Show the customer login screen.
     *
     * Accounts are created by an admin, so there is no public sign up.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Sign a customer in.
     */
    public function login(CustomerLoginRequest $request): RedirectResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Those credentials do not match our records.',
            ]);
        }

        $request->session()->regenerate();

        // A guest who touched an admin URL leaves an "intended" address behind.
        // Honouring it would throw a customer at the admin panel, so drop it
        // and send each account straight to its own side of the system.
        $request->session()->forget('url.intended');

        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('order.create');
    }

    /**
     * Sign the customer out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
