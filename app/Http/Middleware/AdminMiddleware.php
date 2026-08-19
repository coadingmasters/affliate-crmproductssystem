<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Allow only authenticated admins through; everyone else goes to the frontend.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            // Drop any remembered admin URL so a later login is not diverted.
            $request->session()->forget('url.intended');

            return redirect('/');
        }

        return $next($request);
    }
}
