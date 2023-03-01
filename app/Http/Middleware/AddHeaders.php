<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddHeaders
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next) {
        $response = $next($request);
        if (!headers_sent()) {
            if(getControllerName() == 'publish' && getActionName() == 'progress') {
                header_remove("X-FRAME-OPTIONS");
            } elseif(getControllerName() == 'publish' && getActionName() == 'site-delete') {
                header_remove("X-FRAME-OPTIONS");
            } else {
                $response->header("X-FRAME-OPTIONS", "DENY");
            }
            if (getUser()) {
                getUser()->setLastActionTime();
            }
        }

        $response->header('Cache-Control','nocache, no-store, max-age=0, must-revalidate')
        ->header('Pragma','no-cache');

        return $response;
    }
}