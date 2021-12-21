<?php

namespace App\Http\Middleware;

use App\Invitation;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class CanRemoveContact
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        /** @var User $user */
        $user = Auth::user();
        $contact = $user->contacts()->where('contact_id', '=', $request->route('contact')->id)->exists();

        if (! $contact) {
            return redirect()->back();
        }

        return $next($request);
    }
}
