<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;

class AuthorizeUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        $userRole = $user->userRole;

        if ($userRole == null) {
            throw ValidationException::withMessages(["error" => "the current user doesnt have a role."]);
        }

        $abilities = $userRole->role->permissions;

        if ($abilities->isEmpty()) {
            throw ValidationException::withMessages(["error" => "the current role doesnt have a permission"]);
        }

        foreach ($abilities as $ability) {
            if (! $user->tokenCan("$ability->name") ) {
                throw new MissingAbilityException($ability);
            }
        }

        return $next($request);
    }
}
