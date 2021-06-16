<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;
class AdminUserController extends Controller
{
    private $user;
    private $role;
    public function __construct(User $user, Role $role)
    {
        $this->user = $user;
        $this->role = $role;
    }

    public function index()
    {
        $users = $this->user->all();
        $roles = $this->role->all();
        if ($users) {
            return response()->json([
                'user' => $users,
                'role' => $roles,
            ], 200);
        } else {
            return response()->json(['error' => 'Không có bản ghi nào',
                'status' => '500'], 500);
        }
    }

    public function store(Request $request)
    {
        
        try {
           
            DB::beginTransaction();
            //$users = User::find(1);
            $users = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $roleId = $request->role_id;
           
            echo('456');
            $users->roles()->attach($roleId);

            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => 'Lưu xong user',
            ]);
        } catch (Exception $exception) {
            DB::rollBack();

            // return response()->json([
            //     'code'=> 500,
            //     'message' => 'Không lưu được giá vật tư',
            // ]);
            // Call report() method of App\Exceptions\Handler
            $this->reportException($exception);

            // Call render() method of App\Exceptions\Handler
            //$response = $this->renderException($request, $exception);

        }
    }
}
