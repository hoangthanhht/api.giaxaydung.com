<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminRoleController extends Controller
{
    //
    private $role;
    public function __construct(Role $role)
    {
        $this->role = $role;
    }
    public function index () {
        $roles = $this->role->all();
    }

    public function store(Request $request)
    {

        try {

            DB::beginTransaction();
            $roles = Role::create([
                'name' => $request->name,
                'slug' => $request->slug,
            ]);
            
            DB::commit();
            return response()->json([
                'code' => 200,
                'message' => $roles,
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

    public function delete(Request $request, $idRole)
    {

        try {

            DB::beginTransaction();
            $roles = $this->role->find($idRole);
            //$roles->roles()->delete();
            DB::table('users_roles')->where('role_id',$idRole)->delete();
            $roles->delete();
            //dd($roleId);
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa role',
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

    public function update(Request $request, $idRole)
    {
        
        try {
            DB::beginTransaction();
            
            
            $roles = Role::find($idRole)->update([
                'slug' => $request->slug,
                'name' => $request->name,
            ]);
               
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $roles,
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
