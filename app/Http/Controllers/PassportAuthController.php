<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator; 
use App\Http\Requests\ruleRegister;
use App\RemoteException;
use App\Exception;
class PassportAuthController extends Controller
{
    /**
     * Registration
     */
    public function register(ruleRegister $request)
    {

        // $rs = $this->validate($request, [
        //     'name' => 'bail|required|min:4',
        //     'email' => 'required|string|email',
        //     'password' => 'required|min:8',
        // ]);
  
        // $validator = Validator::make($request->all(), [
        //     'name' => 'bail|required|min:4',
        //     'email' => 'required|string|email',
        //     'password' => 'required|min:8',
        // ]);//->validate();
        //$validated = $request->validated();// cái này đung với class request mình tự tạo ra sử dụng validated
        //$errors = $validator->errors();

        
        try {
            
            if (isset($request->validator) && $request->validator->fails()) {
                return response()->json([
                    'error_code'=> 500, 
                    'message'   => $request->validator->errors()->first(),//$validator->errors()->first(),
                    'errors'    => $request->validator->errors() //hoặc $validator->errors()->toArray(),
                ]);
            }
           
            // if ($validator->fails()) {
            //     return response()->json($validator->errors(), 422);
            // }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => 'user',
            ]);
           
            $token = $user->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token], 200);
            // Validate the value...
        } catch (Exception $exception) {
             // Call report() method of App\Exceptions\Handler
            $this->reportException($e);
            
            // Call render() method of App\Exceptions\Handler
            $response = $this->renderException($request, $e);
        
        }
    }
 
    /**
     * Login
     */
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
        
       
        if (auth()->attempt($data)) {
            //$user = Auth::user(); 
            
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;

            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Mật khẩu hoặc password không đúng',
                                     'status'=> '401'
        
        ], 401);

        }
    }   
    public function details() 
    {
        $user = Auth::user(); 
        $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token,'user' => $user], 200); 
    } 
}