<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\User;
use Facade\FlareClient\Http\Response as HttpResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Response as IlluminateHttpResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class VerifyEmailController extends Controller
{
//https://allaravel.com/blog/xac-thuc-tai-khoan-moi-dang-ky-bang-email-trong-laravel//link tham khao tạo verify email tự tạo
use MustVerifyEmail;

    public function verify($id, $hash)// hàm này bắt buộc trùng tên để ghi đè viecck verify trong trait MustVerifyEmail
    {
        $user = User::find($id);
        // abort_if(!$user, 403);
        // abort_if(!hash_equals($hash, sha1($user->getEmailForVerification())), 403);

        if (!$user->hasVerifiedEmail()) {// neu chua xac minh tuong duong voi trong data base null email-at luc nay se danh dau vao o email at 
                                        //vi khi nguoi dung click vao link thi co nghia la se goi den api nay va chay vao ham nay
            $user->markEmailAsVerified();
           
            event(new Verified($user));
        }
        $url = "http://thunghiem.gxd.vn/#/login";
        //return view('welcome');
        return Redirect::to($url);// đẩy đến 1 đường link bên ngoài sau khi người dùng xác minh email
    }

    public function resendNotification(Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return ['message'=> 'OK.'];
    }


    // public function verify($id, $hash): RedirectResponse
    // {
    //     $user = User::find($id);
    //     if (!$user->hasVerifiedEmail()) {
    //         $user->markEmailAsVerified();
    //         event(new Verified($user));
    //         return redirect(env('FRONT_URL') . '/email/verify/already-success');
    //     }


    //     return redirect(env('FRONT_URL') . '/email/verify/success');
    // }


    // public function resend(Request $request)
    // {
    //     $request->user()->sendEmailVerificationNotification();
    //     return view('welcome');
    // }

    // public function verify(EmailVerificationRequest $request)
    // {
    //     $user = User::find($request->id);
    //     if (!$user->hasVerifiedEmail()) {// neu chua xac minh tuong duong voi trong data base null email-at luc nay se danh dau vao o email at 
    //                                     //vi khi nguoi dung click vao link thi co nghia la se goi den api nay va chay vao ham nay
    //         $user->markEmailAsVerified();
           
    //         event(new Verified($user));
    //     }
    //     return view('welcome');
    //     // $request->fulfill();
    //     // return view('welcome');
    // }
}