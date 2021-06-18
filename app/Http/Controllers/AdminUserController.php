<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
        $arrUserSlug = [];
        $arrSlugOfAllUser = [];
        $users = $this->user->all();
        $roles = $this->role->all();
        foreach ($users as $itemUser) {
            foreach ($itemUser->roles()->get() as $item) {
                array_push($arrUserSlug, $item->slug);
            }
            $arrSlugOfOneUser = array($itemUser->id => $arrUserSlug);
            array_push($arrSlugOfAllUser, $arrSlugOfOneUser);
            $arrUserSlug = [];
        }
        if ($users) {
            return response()->json([
                'user' => $users,
                'role' => $roles,
                'role_of_all_user' => $arrSlugOfAllUser,
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
            $users = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $roleId = json_decode($request->role_id);
            //dd($roleId);
            $users->roles()->attach($roleId);
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => $roleId,
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

    public function update(Request $request, $idUser)
    {

        try {

            DB::beginTransaction();
            if (strlen($request->password) >= 6) {

                $users = $this->user->find($idUser)->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                ]);
            } else {

                $users = $this->user->find($idUser)->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);
            }
            $users = $this->user->find($idUser);
            $roleId = json_decode($request->role_id);
            //dd($roleId);
            $users->roles()->sync($roleId);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $roleId,
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

    public function delete(Request $request, $idUser)
    {

        try {

            DB::beginTransaction();
            $users = $this->user->find($idUser);
            //$users->roles()->delete();
            DB::table('users_roles')->where('user_id',$idUser)->delete();
            $users->delete();
            //dd($roleId);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa user',
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
