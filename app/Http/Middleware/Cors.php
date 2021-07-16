<?php

namespace App\Http\Middleware;

use Closure;

class Cors
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
        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, Origin, Text-X',
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Expose-Headers' => 'Content-Length, Access-Control-Allow-Origin, Access-Control-Allow-Credentials ',
            'GXD-Version' => '1.0'
        ];
        if ( $request->getMethod() === 'OPTIONS' ) {
            return response()
            ->json(['status' => 'success'])
            ->withHeaders($headers);
        }
        //echo('123');
        $response = $next($request);

        $response->headers->add( $headers );

        return $response;
    }
}
