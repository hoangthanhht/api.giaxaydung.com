<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\material_cost;
use App\Models\User;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

//header('Access-Control-Allow-Origin', '*');
//header("Access-Control-Allow-Methods: GET, POST");
class giaVatTuController extends Controller
{
    use HelperTrait;
    public function sortTasks($tasks, $columns = ['*'])
    {
        $cases = [];
        $ids = [];
        $params = [];

        foreach ($tasks['data'] as $task) {
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
        $material_cost = new material_cost();
        $user = User::find($idUserImport);
        
        if ($user->can('create-gia-vat-tu')) {
            $user = User::find($idUserImport);
            $arrTemp = [];
            $arrUpdate = [];
            $arrData = json_decode($request->jsonData);
            //$arrData = json_decode($request->,true);// dungf cachs nay thi $arrData se la mang con neu khong co true thi se la object
            $exitsPrice = false;
            $arrDupplicate = [];// mảng chứa các công việc có 6 tiêu chí của $get trùng nhau nhưng chỉ lấy 1 lần thôi
            $arrCheck = [];// mảng xác nhận rằng đã update 1 lần của $get. mảng này sẽ có số phần tử bằng với $arrDupplicate sau khi đã check
            DB::beginTransaction(); // đảm bảo tính toàn vẹn dữ liệu
            try {
                foreach ($arrData as $item) {
                    $count = count(get_object_vars($item)); // dung ham nay de dem so luong cua 1 stdclass object sau khi decode
                    //giaVatTu::create([
                    if ($count >= 8) {// điều kiện bắt trường hợp bảng giá thiếu 1 trong các cột giá , mã hay nguồn thì sẽ lỗi. đây là trường hợp dòng chỉ có 2 - 3 ô
                       
                        $get = DB::table('material_costs')
                            ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
                            ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
                            ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
                        //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
                            ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                            ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
                            ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                            ->where('user_id', $user ? $user->id : null)
                            ->get();
                        // chú ý phuong thức get trả về 1 colection chứ không phải là 1 mảng nên kiểu dữ liệu của $get sẽ không phải mảng
                        //    if($get->isEmpty()) {
                        //       echo('empty($get)');
                        //       echo(gettype($get));
                        //       echo($get->isEmpty());

                        //    }
                        if(count($get) > 1) {//sét trường hợp mà $get có nhiều loại vật tư trùng nhau thì chỉ cho update giá 1 làn khi duyết qua $get 1 vòng 
                            // còn các vòng sau thì khong update giá nữa không thì không update giá đc. ví dụ có 3 công việc 1,2,3 giống hệt nhau trong bảng vật tư
                            // khi đó khi lấy công việc 1 thì $get có 3 phần tử. khi lặp đến công việc 2 thì $get cũng có 3 nên ta chỉ cho update giá khi lặp $get của
                            // công việc 1 thôi
                            $exit = false;
                            foreach ($get as $getItem) {
                                if(count($arrDupplicate)>0) {
                                    foreach ($arrDupplicate as $arrDupplicateItem) {

                                        if(($arrDupplicateItem->maVatTu && $arrDupplicateItem->maVatTu !== "null" ? $arrDupplicateItem->maVatTu : null) == ($getItem->maVatTu && $getItem->maVatTu !== "null" ? $getItem->maVatTu : null)
                                        && ($arrDupplicateItem->tenVatTu && $arrDupplicateItem->tenVatTu !== "null" ? $arrDupplicateItem->tenVatTu : null) == ($getItem->tenVatTu && $getItem->tenVatTu !== "null" ? $getItem->tenVatTu : null)
                                        && ($arrDupplicateItem->donVi && $arrDupplicateItem->donVi !== "null" ? $arrDupplicateItem->donVi : null) == ($getItem->donVi && $getItem->donVi !== "null" ? $getItem->donVi : null)
                                        && ($arrDupplicateItem->nguon && $arrDupplicateItem->nguon !== "null" ? $arrDupplicateItem->nguon : null) == ($getItem->nguon && $getItem->nguon !== "null" ? $getItem->nguon : null)
                                        && ($arrDupplicateItem->ghiChu && $arrDupplicateItem->ghiChu !== "null" ? $arrDupplicateItem->ghiChu : null) == ($getItem->ghiChu && $getItem->ghiChu !== "null" ? $getItem->ghiChu : null)
                                        && ($arrDupplicateItem->tinh && $arrDupplicateItem->tinh !== "null" ? $arrDupplicateItem->tinh : null) == ($getItem->tinh && $getItem->tinh !== "null" ? $getItem->tinh : null))
                                        {
                                            $exit = true;
                                            break;
                                        }
                                    }
                                    if($exit == false) {
                                        
                                            array_push($arrDupplicate,$getItem);
                                            $exit = true;
                                            break;
                                        
                                    }
                                } else {
                                    array_push($arrDupplicate,$getItem);
                                    break;
                                }
                                if($exit == true){
                                    break;
                                }
                            }
                        }
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
                                'user_id' => $user ? $user->id : null,
                                'vote_mark' => $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null,
                                'created_at' => $material_cost->freshTimestamp(),
                                'updated_at' => $material_cost->freshTimestamp(),
                            ]);
                        } else { // truong họp khong trung
                            if(count($get) == 1) {
                                foreach ($get as $getItem) {
                                    $giaDaCo = $getItem->giaVatTu;
                                    $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
                                    $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
                                    $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
    
                                    if ($pos1 !== false) { //tim thay gia im port trong gia da co
                                        
                                        $exitsPrice = true;
                                        //echo($c);
                                        break;
                                    } else { // bổ xung mới giá
                                        $voteDaCo = $getItem->vote_mark;
                                        $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
                                        $giaAfterUpdate = $giaDaCo . ";" . $giaImport;
                                        $voteAfterUpdate = $voteDaCo . ";" . $voteImport;
                                        DB::table('material_costs')
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
                                                'user_id' => $user ? $user->id : null,
                                                'vote_mark' => $voteAfterUpdate,
                                                'updated_at' => $material_cost->freshTimestamp(),
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
                            }

                            if(count($get) > 1 && (count($arrDupplicate) !== count($arrCheck))) {// trường hợp khi chưa update thì $arrDupplicate và arrcheck sẽ có số phần tử k bằng nhau
                                foreach ($get as $getItem) {
                                    $giaDaCo = $getItem->giaVatTu;
                                    $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
                                    $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
                                    $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
    
                                    if ($pos1 !== false) { //tim thay gia import trong gia da co
                                        
                                        $exitsPrice = true;
                                        //echo($c);
                                        break;
                                    } else { // bổ xung mới giá
                                        $voteDaCo = $getItem->vote_mark;
                                        $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
                                        $giaAfterUpdate = $giaDaCo . ";" . $giaImport;
                                        $voteAfterUpdate = $voteDaCo . ";" . $voteImport;
                                        DB::table('material_costs')
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
                                                'user_id' => $user ? $user->id : null,
                                                'vote_mark' => $voteAfterUpdate,
                                                'updated_at' => $material_cost->freshTimestamp(),
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
                                array_push($arrCheck,'okcheck');
                            }
                        }
                        if ($exitsPrice === true) {
                            break;
                        }
                    }
                }
                if ($exitsPrice === true) {
                    if ($agreeOverride === "0") {
                        return response()->json([
                            'code' => 200,
                            'exist' => true,
                            'message' => 'Bản ghi đã tồn tại',
                        ]);
                    }
                    if ($agreeOverride === "1") { // dong y ghi de
                        foreach ($arrData as $item) {
                            //giaVatTu::create([
                            $get = DB::table('material_costs')
                                ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
                                ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
                                ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
                            //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
                                ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                                ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
                                ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                                ->where('user_id', $user ? $user->id : null)
                                ->get();

                            foreach ($get as $getItem) {
                                $giaDaCo = $getItem->giaVatTu;
                                $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
                                $voteDaCo = $getItem->vote_mark;
                                $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
                                $posGia = strpos($giaImport, ':'); // tách giá đến vị trí :
                                $posVote = strpos($voteImport, ':'); // tách giá đến vị trí :
                                $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
                                if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                                    $arrgiaDaCo = explode(';', $giaDaCo);
                                    for ($key = 0; $key < count($arrgiaDaCo); $key++) {
                                        if (strpos($arrgiaDaCo[$key], substr($giaImport, 0, $posGia)) !== false) {
                                            unset($arrgiaDaCo[$key]); //xoa bo phan tu trong mang
                                            break;
                                        }
                                    }
                                    array_push($arrgiaDaCo, $giaImport);
                                    $giaDaCoUpdate = implode(';', $arrgiaDaCo);

                                    $arrvoteDaCo = explode(';', $voteDaCo);
                                    for ($key = 0; $key < count($arrvoteDaCo); $key++) {
                                        if (strpos($arrvoteDaCo[$key], substr($voteImport, 0, $posVote)) !== false) {
                                            unset($arrvoteDaCo[$key]); //xoa bo phan tu trong mang
                                            break;
                                        }
                                    }
                                    array_push($arrvoteDaCo, $voteImport);
                                    $voteDaCoUpdate = implode(';', $arrvoteDaCo);
                                    DB::table('material_costs')
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
                                            'user_id' => $user ? $user->id : null,
                                            'vote_mark' => $voteDaCoUpdate,
                                            'updated_at' => $material_cost->freshTimestamp(),
                                        ]);

                                }
                            }
                        }
                        DB::commit();
                        return response()->json([
                            'code' => 200,
                            'message' => 'Lưu xong giá vật tư',
                        ]);
                    }
                    if ($agreeOverride === "2") { // khong dong y ghi de
                        return;
                    }
                } else {
                    //  $controlUpdate = new giaVatTuController();
                    //  $controlUpdate->sortTasks($arrUpdate);
                    // //giaVatTu::updated($arrUpdate);
                    material_cost::insert($arrTemp); // phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
                    // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
                    // dung eloquen khi dung voi insert thi khong chen dc ngay vao create_at.phai dung ham create thi moi tao dc create_at
                    $arrTemp = [];
                    $arrUpdate = [];
                    DB::commit();
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
        } else {
            return response([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện tác vụ này',
            ], 200);
        }
    }

    public function updateDataGiaVatTu(Request $request, $idBg, $idUser)
    {
        $user = User::find($idUser);
        // $pm = $u->getAllPermissions($u->permissions[0]);
        if ($user->can('edit-gia-vat-tu')) {
            $itemupdate = material_cost::find($idBg);
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
        } else {
            return response([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện tác vụ này',
            ], 200);
        }

    }

    public function getAllDataTableGiaVT()
    {
        $giaVt = material_cost::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json([
            'success' => true,
            'data' => $giaVt,

        ]);
    }

    public function getDataTableGiaVT()
    {
        //$giaVt = giaVatTu::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;
        $giaVt = material_cost::paginate(20);
        return response()->json(
            // 'success' => true,
            // 'data' => $giaVt,
            $giaVt
        );
    }

    public function getListBaoGiaProvince()
    {
        $stringArr = '';
        $getProvince = DB::table('material_costs')->select('tinh')->distinct()->get();
        foreach ($getProvince as $item) {
            $getPrice = DB::table('material_costs')->where('tinh', $item->tinh)->first(); //select('giaVatTu')->distinct()->get();
            //foreach ($getPrice as $itemPrice) {
            $arrgiaProvince = explode(';', $getPrice->giaVatTu);
            foreach ($arrgiaProvince as $itemArr) {
                $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                $str1 = substr($itemArr, 0, $pos);
                $str1 = str_replace(',', '_', $str1);
                $getNameProvince = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
                if ($stringArr === '') {

                    $stringArr = $getNameProvince->name_province . '_' . $getNameProvince->symbol_province . '_' . $str1 . ';';
                } else {

                    $stringArr = $stringArr . $getNameProvince->name_province . '_' . $getNameProvince->symbol_province . '_' . $str1 . ';';
                }
            }
            //}
        }
        $stringArr = substr($stringArr, 0, strlen($stringArr) - 1);
        $arrPriceProvince = explode(";", $stringArr);
        return response()->json($arrPriceProvince, 200);

    }

    public function getPriceWithCodeMaterial($codeMaterial, $stringVT)
    {
        $arr = explode("_", $stringVT);
        $strPrice = $arr[2] . ',' . $arr[3];
        $arrResult = [];
        $getPrice = DB::table('material_costs')
            ->where('tinh', $arr[1])
            ->where('maVatTu', $codeMaterial)
            ->get();
        foreach ($getPrice as $itemPrice) {
            $arrgiaProvince = explode(';', $itemPrice->giaVatTu);
            foreach ($arrgiaProvince as $item) {
                $pos = strpos($item, $strPrice);
                if ($pos !== false) {
                    $pos = strpos($item, ':');
                    $giaVt = substr($item, $pos + 1, strlen($item));
                    $arrTemp = [
                        'Tên vật tư' => $itemPrice->tenVatTu,
                        'Đơn vị' => $itemPrice->donVi,
                        'Nguồn' => $itemPrice->nguon,
                        'Giá vật tư' => $giaVt,
                        'Ghi chú' => $itemPrice->ghiChu,
                    ];
                    array_push($arrResult, $arrTemp);
                    break;
                }
            }
        }
        return response()->json($arrResult, 200);

    }

    public function getPriceWithKeyWord($stringVT, $keyWord)
    {
        $arr = explode("_", $stringVT);
        $strPrice = $arr[2] . ',' . $arr[3];
        $arrResult = [];
        $getPrice = DB::table('material_costs')
            ->where('tinh', $arr[1])
            ->get();
        foreach ($getPrice as $itemPrice) {

            $pos = strpos(strtolower($this->convert_vi_to_en($itemPrice->tenVatTu)), strtolower($this->convert_vi_to_en($keyWord)));
            if ($pos !== false) {
                $arrgiaProvince = explode(';', $itemPrice->giaVatTu);
                foreach ($arrgiaProvince as $item) {
                    $pos = strpos($item, $strPrice);
                    if ($pos !== false) {
                        $pos = strpos($item, ':');
                        $giaVt = substr($item, $pos + 1, strlen($item));
                        $arrTemp = [
                            'Tên vật tư' => $itemPrice->tenVatTu,
                            'Đơn vị' => $itemPrice->donVi,
                            'Nguồn' => $itemPrice->nguon,
                            'Giá vật tư' => $giaVt,
                            'Ghi chú' => $itemPrice->ghiChu,
                        ];
                        array_push($arrResult, $arrTemp);
                        break;
                    }
                }
            }
        }
        return response()->json($arrResult, 200);

    }
}
