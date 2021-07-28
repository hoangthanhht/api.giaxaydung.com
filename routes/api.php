<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PassportAuthController;
use App\Http\Controllers\ReportDayController;
use App\Http\Controllers\linkQldaController;
use App\Http\Controllers\giaVatTuController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminRoleController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\AdminConfigSystem;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\material_cost_for_guestController;
use App\Http\Controllers\ArticlePostController;

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
    
    // đổi mật khẩu cho nguoi dung khi da dang nhap
    Route::post('changePassAfterLogin', [ResetPasswordController:: class,'changePass']);
    // upload avatar và lưu vao database
    Route::post('upload', [PassportAuthController::class, 'upload']);
    Route::get('details', [PassportAuthController::class, 'details']);
    // route này sẽ gửi lại link xác minh
    Route::post('/email/verification-notification', [VerifyEmailController::class, 'resendNotification'])
    ->name('verification.send');
    });
// route này sẽ hiện ra khi người dùng click vào link xác minh
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->middleware(['signed'])->name('verification.verify');
//api gửi báo cáo ngày tư vấn giam sát
Route::middleware('auth:api')->group(function () {
    Route::resource('post/bcday', ReportDayController::class);
});
//=======================================\\//================================//
// trả đường dẫn theo mahcv trả về cho phàn mềm
Route::get('mhcv/{id}', [linkQldaController::class, 'show']);
//lấy đường dân về từ trang qlda
Route::post('link', [linkQldaController::class, 'store']);
// tạo bảng định mức từ link lấy về từ qlda
Route::post('createTableLDm', [linkQldaController::class, 'storeTableDM']);
//lây du liệu từ bảng để hiển thị ra view front end co pagination
Route::get('getDataTableDm', [linkQldaController::class, 'getDataTableDM']);
//lây du liệu từ bảng để hiển thị ra view front end co pagination
Route::get('getDataTableDmContribute', [linkQldaController::class, 'getDataTableDmContribute']);

//lây tat ca du liệu từ bảng để hiển thị ra view front end khong co pagination
Route::get('getAllDataTableDm', [linkQldaController::class, 'getAllDataTableDm']);
//lây tat ca du liệu từ bảng nguoi dung đóng góp để hiển thị ra view front end khong co pagination
Route::get('getAllDataTableDmContribute', [linkQldaController::class, 'getAllDataTableDmContribute']);

//api đẻ chỉnh sửa đinh mức
Route::post('updateDataDm/{id}/{iduser}', [linkQldaController::class, 'updateDataDm']);
//api đẻ chỉnh sửa đinh mức contribute
Route::post('updateDataDmContribute/{id}/{iduser}', [linkQldaController::class, 'updateDataDmContribute']);
//api đẻ chỉnh sửa đinh mức contribute
Route::post('CreateDinhMucContribute', [linkQldaController::class, 'CreateDinhMucContribute']);

// trả ghi chú đinh mức cho phần mềm
Route::get('noteDm/{id}', [linkQldaController::class, 'getNoteDM']);
//api đê dưa mã công việc(mã con) vào dữ liệu 
Route::post('CreateDinhMucFromFile/{iduser}', [linkQldaController::class, 'CreateDinhMuc']);
//api đê dưa mã công việc(mã con) vào dữ liệu 
Route::post('handleDeleteNoteDmContribute/{iddm}', [linkQldaController::class, 'handleDeleteNoteDmContribute']);
//api đê approve dinh muc
Route::post('handleApproveContribute', [linkQldaController::class, 'handleApprove']);




//====================================\\//====================================//
// đưa dữ liệu từ bảng excel vào data base
Route::post('createGiaVT/{idUser}/{agreeOverride}', [giaVatTuController::class, 'store']);
/* PHẦN UP GIÁ CỦA NGƯỜI DÙNG VÀ APPROVE CỦA QUẢN TRỊ */
// đưa dữ liệu từ bảng excel vào data base do người dùng up
Route::post('guestCreateGiaVT/{idUser}/{agreeOverride}', [material_cost_for_guestController::class, 'store']);
// lấy thông tin những người đã up giá
Route::post('getUserUpBaoGia', [material_cost_for_guestController::class, 'getUserUpBaoGia']);
// lấy tỉnh mà 1 người đã up giá
Route::post('getInfoTinhBaoGiaOfUser', [material_cost_for_guestController::class, 'getInfoTinhBaoGiaOfUser']);
// lấy dữ liệu về cảu 1 người up và của 1 địa phương
Route::get('viewBaoGiaWithSelecttion/{user_id}/{tinh}/{khuvuc}/{thoidiem}/{check}/{user_id_view}/{agreebuy}', [material_cost_for_guestController::class, 'viewBaoGiaWithSelecttion']);
// tạm thời khongodungf route này
Route::get('getDataTableGiaVTGuest', [material_cost_for_guestController::class, 'getDataTableGiaVTGuest']);
// lấy những thông tin còn lại theo sự lựa chọn thành phố và người đăng
Route::post('getInfoBaoGiaOfUser', [material_cost_for_guestController::class, 'getInfoBaoGiaOfUser']);
// lấy những thông tin còn lại theo sự lựa chọn thành phố và người đăng
Route::post('updateDataGiaVatTuUserUp/{id}/{iduser}', [material_cost_for_guestController::class, 'updateDataGiaVatTuUserUp']);
// lấy những thông tin còn lại theo sự lựa chọn thành phố và người đăng phục vụ search trong approve
Route::post('baoGiaWithSelecttionForSearchApprove', [material_cost_for_guestController::class, 'BaoGiaWithSelecttionForSearchApprove']);
// lấy những thông tin còn lại theo sự lựa chọn thành phố và người đăng phục vụ search trong approve
Route::post('approveGiaVt/{idUserApprove}/{agreeOverride}', [material_cost_for_guestController::class, 'approve']);
// api sử lý cho like
Route::post('handleLike', [material_cost_for_guestController::class, 'handleLike']);
// api sử lý xoa bao gia cua nguoi approve bao gia
Route::post('deleteBaoGia', [material_cost_for_guestController::class, 'deleteBaoGia']);
// lấy những thông tin thoi diem theo sự lựa chọn thành phố và người đăng
Route::post('getThoiDiemBaoGiaOfUser', [material_cost_for_guestController::class, 'getThoiDiemBaoGiaOfUser']);

//=======================================\\//==========================================================//






/* PHẦN LƯU VÀ CHỈNH SỬA TRONG BẢNG CHÍNH CỦA LƯU VẬT TƯ */
//api đẻ chỉnh sửa gia vật tư
Route::post('updateDataGiaVatTu/{id}/{iduser}', [giaVatTuController::class, 'updateDataGiaVatTu']);
// lấy dữ liệu giá co phan trang về từ data base
Route::get('getDataTableBaoGia', [giaVatTuController::class, 'getDataTableGiaVT']);
// lấy tat ca dữ liệu giá về từ data base
Route::get('getAllDataTableGiaVT', [giaVatTuController::class, 'getAllDataTableGiaVT']);
// lấy danh sách các tỉnh hiện đang có trong data base
Route::get('getListBaoGia', [giaVatTuController::class, 'getListBaoGiaProvince']);
// trả về cho phần mềm báo giá theo mã
Route::get('getPriceWithCodeMaterial/{codeMaterial}/{stringVT}', [giaVatTuController::class, 'getPriceWithCodeMaterial']);
// trả về cho phần mềm báo giá theo từ khóa
Route::get('getPriceWithKeyWord/{stringVT}/{keyWord}', [giaVatTuController::class, 'getPriceWithKeyWord']);
/* PHẦN API CHO USER */
// lấy dữ liệu user và role về từ data base
Route::get('getDataTableUser', [AdminUserController::class, 'index']);
// lấy dữ liệu role về từ data base
Route::get('getDataTableRole', [AdminRoleController::class, 'index']);
// tao user va role
Route::post('createUser', [AdminUserController::class, 'store']);
// edit user va role
Route::post('updateUser/{id}', [AdminUserController::class, 'update']);
// delete user va role
Route::post('deleteUser/{id}', [AdminUserController::class, 'delete']);

// edit role
Route::post('updateRole/{id}', [AdminRoleController::class, 'update']);

// delete role
Route::post('deleteRole/{id}', [AdminRoleController::class, 'delete']);

// tao role
Route::post('createRole', [AdminRoleController::class, 'store']);

// thay doi file env
Route::post('changeEnvironment', [AdminConfigSystem::class, 'changeEnvironment']);
// bảo trì hệ thống
Route::post('offSystem', [AdminConfigSystem::class, 'offSystem']);
// mở lại hệ thống
Route::post('onSystem', [AdminConfigSystem::class, 'onSystem']);


// gửi email reset pass
Route::post('sendEmailResetPassword', [ResetPasswordController:: class,'sendMail']);
// đổi mật khẩu
Route::post('changePass', [ResetPasswordController:: class,'reset']);
// lấy đường dãn file avatar
Route::get('getPathFile/{id}', [PassportAuthController::class, 'getPathFile']);
/* API CHO DANG BAI VIET */
// tao bai viết
Route::post('createArticle', [ArticlePostController::class, 'createArticle']);
// lấy bai dang co phan trang
Route::get('getListArticle', [ArticlePostController::class, 'getListArticle']);




Route::get('test', [PassportAuthController::class, 'test']);