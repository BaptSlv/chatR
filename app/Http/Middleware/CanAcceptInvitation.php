<?php

namespace App\Http\Middleware;

use App\Invitation;
use Closure;
use Illuminate\Support\Facades\Auth;

class CanAcceptInvitation
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
        $invitation = Invitation::query()->where('from_id', '=', $request->route('contact')->id)->where('to_id', '=', Auth::user()->id)->exists();

        if (! $invitation) {
            return redirect()->back();
        }

        return $next($request);
    }
}
