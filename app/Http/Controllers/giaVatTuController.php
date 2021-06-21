<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\giaVatTu;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//header('Access-Control-Allow-Origin', '*');
//header("Access-Control-Allow-Methods: GET, POST");
class giaVatTuController extends Controller
{


    public function sortTasks($tasks, $columns = ['*'])
    {
        $cases = [];
        $ids = [];
        $params = [];

        foreach ($tasks['data'] as  $task) {
            $id = (int) $task['id'];
            $cases[] = "WHEN {$id} then ?";
            $params[] = $task['name'];
            $ids[] = $id;
        }
        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);

        return DB::update("UPDATE `tasks` SET `name` = CASE `id` {$cases} END
            WHERE `id` in ({$ids})", $params);
    }

    public function store(Request $request, $idUserImport, $agreeOverride)
    {
        $user = User::find($idUserImport);
        $arrTemp = [];
        $arrUpdate = [];
        $arrData = json_decode($request->jsonData);
        $exitsPrice = false;
        DB::beginTransaction(); // đảm bảo tính toàn vẹn dữ liệu
        try {
            foreach ($arrData as $item) {
                //giaVatTu::create([
                $get = DB::table('gia_vat_tus')
                    ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
                    ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
                    ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
                    ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                    ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
                    ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                    ->get();
                // chú ý phuong thức get trả về 1 colection chứ không phải là 1 mảng nên kiểu dữ liệu của $get sẽ không phải mảng
                //    if($get->isEmpty()) {
                //       echo('empty($get)');
                //       echo(gettype($get));
                //       echo($get->isEmpty());

                //    }
                if ($get->isEmpty()) { // không tìm thấy bản ghi nào trùng

                    array_push($arrTemp, [
                        'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
                        'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
                        'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
                        'giaVatTu' => $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null,
                        'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                        'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
                        'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                        'tacGia' => $user ? $user->name : null,
                    ]);
                } else { // truong họp trùng
                    if ($agreeOverride) { // đồng ý ghi đè
                        foreach ($get as $getItem) {
                            $giaDaCo = $getItem->giaVatTu;
                            $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
                            $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
                            $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có

                            if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                                $exitsPrice = true;
                                break;
                            } else { // bổ xung mới giá

                                $giaAfterUpdate = $giaDaCo . ";" . $giaImport;
                                DB::table('gia_vat_tus')
                                    ->where('id', $getItem->id)
                                    ->update([
                                        'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
                                        'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
                                        'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
                                        'giaVatTu' => $giaAfterUpdate,
                                        'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                        'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
                                        'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                        'tacGia' => $user ? $user->name : null,
                                    ]);
                                // array_push($arrUpdate, [ 
                                //             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
                                //             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
                                //             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
                                //             'giaVatTu' => $giaAfterUpdate,
                                //             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                //             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
                                //             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                //             'tacGia' => $user ? $user->name : null,
                                //             'id' => $getItem->id
                                // ]);
                            }

                        }
                    } else {
                        foreach ($get as $getItem) {
                            $giaDaCo = $getItem->giaVatTu;
                            $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
                            $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
                            $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
                            if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                                $arrgiaDaCo = explode(';', $giaDaCo);
                                for( $key = 0;  $key < count($arrgiaDaCo);  $key++ )
                                {
                                    if(strpos($arrgiaDaCo[$key], substr($giaImport, 0, $pos))!== false) {
                                        unset($arrgiaDaCo[$key]);
                                        break;
                                    }
                                }
                                array_push($arrgiaDaCo, $giaImport);
                                $giaDaCoUpdate = implode(';',$arrgiaDaCo);
                                DB::table('gia_vat_tus')
                                    ->where('id', $getItem->id)
                                    ->update([
                                        'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
                                        'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
                                        'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
                                        'giaVatTu' => $giaDaCoUpdate,
                                        'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                        'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
                                        'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                        'tacGia' => $user ? $user->name : null,
                                    ]);
                        //         array_push($arrUpdate, [ 
                        //             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
                        //             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
                        //             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
                        //             'giaVatTu' => $giaDaCoUpdate,
                        //             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                        //             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
                        //             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                        //             'tacGia' => $user ? $user->name : null,
                        //             'id' => $getItem->id
                        // ]);
                            }
                        }
                    }
                }
                if ($exitsPrice === true) {
                    break;
                }
            }
            if ($exitsPrice === true) {
                return response()->json([
                    'code' => 200,
                    'exist' => true,
                    'message' => 'Bản ghi đã tồn tại',
                ]);
            } else {
                //  $controlUpdate = new giaVatTuController();
                //  $controlUpdate->sortTasks($arrUpdate);
                // //giaVatTu::updated($arrUpdate);
                giaVatTu::insert($arrTemp); // phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
                // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
                DB::commit();
                $arrTemp = [];
                $arrUpdate = [];
                return response()->json([
                    'code' => 200,
                    'message' => 'Lưu xong giá vật tư',
                ]);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $this->reportException($exception);

            $response = $this->renderException($request, $exception);

        }
    }


    public function updateDataGiaVatTu(Request $request, $iddm)
    {
       
        // $pm = $u->getAllPermissions($u->permissions[0]);
        //if ($user->can('create-tasks')) {
            $itemupdate = giaVatTu::find($iddm);
            if (!$itemupdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                ], 400);
            }

            $updated = $itemupdate->fill($request->all())->save();
            if ($updated) {
                return response()->json([
                    'success' => true,
                    'data' => $request->all(),
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Post can not be updated',
                ], 500);
            }
        // }
        // else {
        //     return response([
        //         'success' => false,
        //         'message' => 'Bạn không có quyền thực hiện tác vụ này'
        //     ],200);
        // }

    }

    public function getDataTableGiaVT()
    {
        $giaVt = giaVatTu::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json([
            'success' => true,
            'data' => $giaVt,
        ]);
    }
}
