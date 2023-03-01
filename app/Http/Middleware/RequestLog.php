<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;

class RequestLog
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

        if ($request->method() === 'GET') {
            Log::info('Request GET: ' . $request->getRequestUri());
        } else {
            Log::info('Request POST: ' . $request->getRequestUri());
        }

        $parameters = $request->all();
        unset($parameters['controller'], $parameters['action'], $parameters['module']);
        if (isset($parameters['_token'])) {
            $parameters['_token'] = '-'; // パスワード文字列は隠蔽する
        }

        Log::info('Request parameters: ' . http_build_query($parameters));

        return $response;
    }
}