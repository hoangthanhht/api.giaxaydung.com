<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

class CheckForMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    // ngoại trừ các đường link sau vẫn được hoạt động khi hệ thống bảo trì
    protected $except = [
        '/api/login',
        '/api/offSystem',
        '/api/onSystem'     
    ];
    protected $app;
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance() && (!$this->isAdminRequest($request) || !$this->isAdminIpAdress($request))) {
            return response('Website đang bảo trì', 503);
        }
        return $next($request);
    }
    private function isAdminIpAdress($request)
    {
        return !in_array($request->ip(), ['14.162.167.166', '42.112.111.20']);
    }
    private function isAdminRequest($request)
    {

        //return ($request->is('quan-tri/*') or $request->is('/'));
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }
            if ($request->is($except)) {
               if($request->email === "user1@gmail.com")// bặt điều kiện cho 1 user nào đó vẫn được phép vào hệ thống khi bảo dưỡng
               {
                   return true;
               }
               return true;
            }
        }

        return false;
    }
}
