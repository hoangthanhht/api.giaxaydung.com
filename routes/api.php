<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\ReportDayController;
use App\Http\Controllers\linkQldaController;
use App\Http\Controllers\giaVatTuController;
use App\Http\Controllers\AdminUserController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [PassportAuthController::class, 'register']);
Route::post('login', [PassportAuthController::class, 'login']);
Route::group(['middleware' => 'auth:api'], function(){
    Route::get('details', [PassportAuthController::class, 'details']);
    });

Route::middleware('auth:api')->group(function () {
    Route::resource('post/bcday', ReportDayController::class);
});
// trả đường dẫn theo mahcv trả về cho phàn mềm
Route::get('mhcv/{id}', [linkQldaController::class, 'show']);
//lấy đường dân về từ trang qlda
Route::post('link', [linkQldaController::class, 'store']);
// tạo bảng định mức từ link lấy về từ qlda
Route::post('createTableLDm', [linkQldaController::class, 'storeTableDM']);
//lây du liệu từ bảng để hiển thị ra view front end
Route::get('getDataTableDm', [linkQldaController::class, 'getDataTableDM']);
//api đẻ chỉnh sửa đinh mức
Route::post('updateDataDm/{id}/{iduser}', [linkQldaController::class, 'updateDataDm']);
// trả ghi chú đinh mức cho phần mềm
Route::get('noteDm/{id}', [linkQldaController::class, 'getNoteDM']);
// đưa dữ liệu từ bảng excel vào data base
Route::post('createGiaVT/{idUser}/{agreeOverride}', [giaVatTuController::class, 'store']);
//api đẻ chỉnh sửa gia vật tư
Route::post('updateDataGiaVatTu/{id}', [giaVatTuController::class, 'updateDataGiaVatTu']);
// lấy dữ liệu giá về từ data base
Route::get('getDataTableBaoGia', [giaVatTuController::class, 'getDataTableGiaVT']);
// lấy dữ liệu user và role về từ data base
Route::get('getDataTableUser', [AdminUserController::class, 'index']);
// tao user va role
Route::post('createUser', [AdminUserController::class, 'store']);
// edit user va role
Route::post('updateUser/{id}', [AdminUserController::class, 'update']);
// delete user va role
Route::post('deleteUser/{id}', [AdminUserController::class, 'delete']);






Route::get('test', [PassportAuthController::class, 'test']);