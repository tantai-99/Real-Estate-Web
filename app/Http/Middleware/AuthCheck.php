<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCheck
{
    const ADMIN_LOGIN = '/admin/auth/login?r=1';
    const CMS_LOGIN = '/auth/login?r=1';
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
        if (getRequestInfo()['controller'] != 'auth') {
            $module = getRequestInfo()['module'];
            $guard = Auth::guard($module)->check();
            if(!$guard || getUser()->checkLastActiontime() === false)  {
                if ($guard) {
                    getUser()->logout();
                    session()->flush();
                }
                // getUser()->logout();
                switch ($module) {
                    case 'admin':
                        return redirect(self::ADMIN_LOGIN);
                        break;
                    case 'default':
                        if(getUser()->getAdminProfile()) {
                            return $next($request);
                        } else {
                            $request->session()->put('previous_link',$request->getRequestUri());
                            return redirect(self::CMS_LOGIN);
                        }
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
        }

        return $next($request);
    }
}
