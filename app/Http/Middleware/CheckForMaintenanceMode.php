<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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
        '/api/onSystem',
        '/api/details',
        
    ];
    protected $app;
    public $checkAdmin ;
    public function __construct(Application $app)
    {
        $this->app = $app;
        //$this->middleware('auth:api');
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
            if ($request->email === "user1@gmail.com") // bặt điều kiện cho 1 user nào đó vẫn được phép vào hệ thống khi bảo dưỡng
            {
                //$this->checkAdmin = 1;
                Auth::attempt(['email' => $request->email, 'password' => $request->password], true);
               
            }
            $user = User::where('email', $request->email? $request->email :'user1@gmail.com')->first();

            $isLogin = $user->remember_token;
            if ($request->is($except) && $isLogin!==''&& $isLogin!== null) {
                return true;
            }
        }

        return false;
    }
}
