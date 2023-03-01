<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CreatorCheck
{   
    const CREATOR_LOGIN = '/creator/login?r=1';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {   
       if(getUser()->getAdminProfile()) {
            return $next($request);
        } else {
            $actionName = getActionName();
            if ($actionName != 'login') {
                return Redirect::to('/creator/login?r=1');
            }
            return $next($request);
        }
    }
}
