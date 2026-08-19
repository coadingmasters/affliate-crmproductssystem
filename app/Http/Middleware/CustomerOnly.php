<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerOnly
{
    /**
     * Keep the storefront for customers.
     *
     * Admins belong in the admin panel, so they are sent there rather than
     * being shown the customer order form.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isAdmin()) {
            $request->session()->forget('url.intended');

            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
