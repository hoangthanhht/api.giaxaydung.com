<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    private $user;
    private $role;
    private $permission;
    public function __construct(User $user, Role $role, Permission $permission)
    {
        $this->user = $user;
        $this->role = $role;
        $this->permission = $permission;
    }

    public function index()
    {
        $arrUserSlugRole = [];
        $arrUserSlugPermisson = [];
        $arrSlugRoleOfAllUser = [];
        $arrSlugPermissOfAllUser = [];
        $users = $this->user->all();
        $roles = $this->role->all();
        $permissions = $this->permission->all();
        foreach ($users as $itemUser) {
            foreach ($itemUser->roles()->get() as $item) {
                array_push($arrUserSlugRole, $item->slug);
            }
            $arrSlugOfOneUser = array($itemUser->id => $arrUserSlugRole);
            array_push($arrSlugRoleOfAllUser, $arrSlugOfOneUser);
            $arrUserSlugRole = [];
            // lấy permission
            foreach ($itemUser->permissions()->get() as $item) {
                array_push($arrUserSlugPermisson, $item->slug);
            }
            $arrSlugPermissOfOneUser = array($itemUser->id => $arrUserSlugPermisson);
            array_push($arrSlugPermissOfAllUser, $arrSlugPermissOfOneUser);
            $arrUserSlugPermisson = [];
        }
        if ($users) {
            return response()->json([
                'user' => $users,
                'role' => $roles,
                'permission' => $permissions,
                'role_of_all_user' => $arrSlugRoleOfAllUser,
                'permission_of_all_user' => $arrSlugPermissOfAllUser,
            ], 200);
        } else {
            return response()->json(['error' => 'Không có bản ghi nào',
                'status' => '500'], 500);
        }
    }

    public function store(Request $request)
    {

        try {
           // dd($request->name);
            DB::beginTransaction();
            $users = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $roleId = json_decode($request->role_id);
            $permissionId = json_decode($request->permission_id);
            //dd($roleId);
            $users->roles()->attach($roleId);
            $users->permissions()->attach($permissionId);
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
            $permissionId = json_decode($request->permission_id);
            //dd($roleId);
            $users->roles()->sync($roleId);// xóa role trong bảng trung gian
            $users->permissions()->sync($permissionId);// xóa permis trong bảng trung gian
            DB::commit();
            return response()->json([
                'success' => true,
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

    public function delete(Request $request, $idUser)
    {

        try {

            DB::beginTransaction();
            $users = $this->user->find($idUser);
            //$users->roles()->delete();
            DB::table('users_roles')->where('user_id',$idUser)->delete();
            DB::table('users_permissions')->where('user_id',$idUser)->delete();
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
