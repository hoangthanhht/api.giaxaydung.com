<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\PasswordReset;
//use Illuminate\Auth\Events\PasswordReset;
use App\Notifications\ResetPasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    public function sendMail(Request $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $passwordReset = PasswordReset::updateOrCreate([
            'email' => $user->email,
        ], [
            'token' => Str::random(60),
        ]);
        if ($passwordReset) {
            $user->notify(new ResetPasswordRequest($passwordReset->token));
        }
  
        return response()->json([
        'message' => 'Mail đã được gửi vào hòm thư của bạn. vui lòng check mail làm theo hướng dẫn'
        ]);


        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );

        // if ($status == Password::RESET_LINK_SENT) {
        //     return [
        //         'status' => __($status)
        //     ];
        // }

        // throw ValidationException::withMessages([
        //     'email' => [trans($status)],
        // ]);
    }

    public function reset(Request $request)
    {
        $passwordReset = PasswordReset::where('token', $request->token)->firstOrFail();
        // if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
        //     $passwordReset->delete();

        //     return response()->json([
        //         'message' => 'This password reset token is invalid.',
        //     ], 422);
        // }
        $user = User::where('email', $passwordReset->email)->firstOrFail();
        $updatePasswordUser = $user->update(['password'=>bcrypt($request->password)]);
        //$passwordReset->delete();
        return response()->json([
            'success' => $updatePasswordUser,
        ]);
        // $status = Password::reset(
        //     $request->only('email', 'password', 'password_confirmation', 'token'),
        //     function ($user) use ($request) {
        //         $user->forceFill([
        //             'password' => Hash::make($request->password),
        //             'remember_token' => Str::random(60),
        //         ])->save();
        //         $user->tokens()->delete();
        //         event(new PasswordReset($user));
        //     }
        // );

        // if ($status == Password::PASSWORD_RESET) {
        //     return response([
        //         'message'=> 'Password reset successfully'
        //     ]);
        // }

        // return response([
        //     'message'=> __($status)
        // ], 500);
    }

    public function changePass(Request $request)
    {
        $user = Auth::user(); 
        // $a = $request->current_password;
        // $a1 = $request->verify_password;
        // $a2 = $request->new_password;
        // $a3 = $user->password;

        if(password_verify($request->current_password, $user->password)) {
            $updatePasswordUser = $user->update(['password'=>bcrypt($request->new_password)]);
            //$passwordReset->delete();
            return response()->json([
                'success' => $updatePasswordUser,
                'message' => 'Thay đổi mật khẩu thành công'
            ]);
        }else {
            return response()->json([
                'success' => false,
                'error' => 'Mật khẩu cũ không đúng'
            ]);
        }
 
    }
}
