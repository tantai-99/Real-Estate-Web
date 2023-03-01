<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IpCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $config = getConfigs('admin.ip');
        if (!empty($config->ip)) {
            $isAccess = false;
            $myIp = $_SERVER['REMOTE_ADDR']; // not ELB
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ips = explode( "," , $_SERVER [ 'HTTP_X_FORWARDED_FOR' ]);
                $ips = array_map('trim', $ips);
                $myIp = $ips[0]; // ELB
            }
            foreach ($config->ip as $ip) {
                if ($myIp == $ip) {
                    $isAccess = true;
                    break;
                }
            }
            if (!$isAccess) {
                header('HTTP/1.0 403 Forbidden');
                die();
            }
        }
        return $next($request);
    }
}
