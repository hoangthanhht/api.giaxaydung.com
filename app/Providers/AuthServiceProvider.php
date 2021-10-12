<?php

namespace App\Providers;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use App\Models\Permission;
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
         'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        // đặt thời hạn cho token
        // Passport::tokensExpireIn(Carbon::now()->addDays(15));

        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        // Passport::pruneRevokedTokens();// xoa token het han


        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Xác thực tài khoản')
                ->line('Nhấn vào nút phía dưới để kích hoạt tài khoản của bạn')
                ->action('Click vào đây', $url);
        });

        // Permission::get()->map(function($permission){
        //     Gate::define($permission->slug, function($user) use ($permission){
        //         $a = $user->hasPermission($permission);
        //        return $user->hasPermission($permission);
        //     });
        // });
    }
}
