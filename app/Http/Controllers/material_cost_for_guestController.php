<?php

namespace App\Http\Controllers;

use App\Models\material_cost;
use App\Models\material_cost_for_guest;
use App\Models\User;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class material_cost_for_guestController extends Controller
{
    use HelperTrait;
    public function store(Request $request, $idUserImport, $agreeOverride)
    {
        $material_cost = new material_cost_for_guest();
        $user = User::find($idUserImport);
            $user = User::find($idUserImport);
            $arrTemp = [];
            $arrUpdate = [];
            $arrData = json_decode($request->jsonData);
            //$arrData = json_decode($request->,true);// dungf cachs nay thi $arrData se la mang con neu khong co true thi se la object
            $exitsPrice = false;
            $arrDupplicate = []; // mảng chứa các công việc có 6 tiêu chí của $get trùng nhau nhưng chỉ lấy 1 lần thôi
            $arrCheck = []; // mảng xác nhận rằng đã update 1 lần của $get. mảng này sẽ có số phần tử bằng với $arrDupplicate sau khi đã check
            DB::beginTransaction(); // đảm bảo tính toàn vẹn dữ liệu
            try {
                foreach ($arrData as $item) {
                    $count = count(get_object_vars($item)); // dung ham nay de dem so luong cua 1 stdclass object sau khi decode
                    //giaVatTu::create([
                    if ($count >= 8) { // điều kiện bắt trường hợp bảng giá thiếu 1 trong các cột giá , mã hay nguồn thì sẽ lỗi. đây là trường hợp dòng chỉ có 2 - 3 ô

                        $get = DB::table('material_cost_for_guests')
                            ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
                            ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
                            ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
                        //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
                            ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                            ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
                            ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                            ->where('user_id', $user ? $user->id : null) // check cả id user cho trương hợp người khác up mà cũng cùng các yếu tố trên
                            ->get();
                        // chú ý phuong thức get trả về 1 colection chứ không phải là 1 mảng nên kiểu dữ liệu của $get sẽ không phải mảng
                        //    if($get->isEmpty()) {
                        //       echo('empty($get)');
                        //       echo(gettype($get));
                        //       echo($get->isEmpty());

                        //    }
                        if (count($get) > 1) { //sét trường hợp mà $get có nhiều loại vật tư trùng nhau thì chỉ cho update giá 1 làn khi duyết qua $get 1 vòng
                            // còn các vòng sau thì khong update giá nữa không thì không update giá đc. ví dụ có 3 công việc 1,2,3 giống hệt nhau trong bảng vật tư
                            // khi đó khi lấy công việc 1 thì $get có 3 phần tử. khi lặp đến công việc 2 thì $get cũng có 3 nên ta chỉ cho update giá khi lặp $get của
                            // công việc 1 thôi
                            $exit = false;
                            foreach ($get as $getItem) {
                                if (count($arrDupplicate) > 0) {
                                    foreach ($arrDupplicate as $arrDupplicateItem) {

                                        if (($arrDupplicateItem->maVatTu && $arrDupplicateItem->maVatTu !== "null" ? $arrDupplicateItem->maVatTu : null) == ($getItem->maVatTu && $getItem->maVatTu !== "null" ? $getItem->maVatTu : null)
                                            && ($arrDupplicateItem->tenVatTu && $arrDupplicateItem->tenVatTu !== "null" ? $arrDupplicateItem->tenVatTu : null) == ($getItem->tenVatTu && $getItem->tenVatTu !== "null" ? $getItem->tenVatTu : null)
                                            && ($arrDupplicateItem->donVi && $arrDupplicateItem->donVi !== "null" ? $arrDupplicateItem->donVi : null) == ($getItem->donVi && $getItem->donVi !== "null" ? $getItem->donVi : null)
                                            && ($arrDupplicateItem->nguon && $arrDupplicateItem->nguon !== "null" ? $arrDupplicateItem->nguon : null) == ($getItem->nguon && $getItem->nguon !== "null" ? $getItem->nguon : null)
                                            && ($arrDupplicateItem->ghiChu && $arrDupplicateItem->ghiChu !== "null" ? $arrDupplicateItem->ghiChu : null) == ($getItem->ghiChu && $getItem->ghiChu !== "null" ? $getItem->ghiChu : null)
                                            && ($arrDupplicateItem->tinh && $arrDupplicateItem->tinh !== "null" ? $arrDupplicateItem->tinh : null) == ($getItem->tinh && $getItem->tinh !== "null" ? $getItem->tinh : null)) {
                                            $exit = true;
                                            break;
                                        }
                                    }
                                    if ($exit == false) {

                                        array_push($arrDupplicate, $getItem);
                                        $exit = true;
                                        break;

                                    }
                                } else {
                                    array_push($arrDupplicate, $getItem);
                                    break;
                                }
                                if ($exit == true) {
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
                            if (count($get) == 1) {
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
                                        DB::table('material_cost_for_guests')
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

                            if (count($get) > 1 && (count($arrDupplicate) !== count($arrCheck))) { // trường hợp khi chưa update thì $arrDupplicate và arrcheck sẽ có số phần tử k bằng nhau
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
                                        DB::table('material_cost_for_guests')
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
                                array_push($arrCheck, 'okcheck');
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
                            $get = DB::table('material_cost_for_guests')
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
                                    DB::table('material_cost_for_guests')
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
                    material_cost_for_guest::insert($arrTemp); // phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
                    // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
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
    
    }
    // hàm lấy ra thông tin của người đăng báo giá. thông tin về khu vực thời điểm....của báo giá
    public function getUserUpBaoGia(Request $request)
    {
        if ($request->check === 0) {
            $getUserId = DB::table('material_cost_for_guests')->select('user_id')->distinct()->get();
            $arrUser = [];
            foreach ($getUserId as $itemUser) {
                $getUserName = DB::table('material_cost_for_guests')->where('user_id', $itemUser->user_id)->select('tacGia')->distinct()->get();
                foreach ($getUserName as $itemUserName) {

                    $temp = array('value' => $itemUser->user_id, 'text' => $itemUserName->tacGia);
                    array_push($arrUser, $temp);
                }

            }

            return response()->json($arrUser, 200);
        } else if ($request->check === 1) {
            $getUserId = DB::table('material_costs')->select('user_id')->distinct()->get();
            $arrUser = [];
            foreach ($getUserId as $itemUser) {
                $getUserName = DB::table('material_costs')->where('user_id', $itemUser->user_id)->select('tacGia')->distinct()->get();
                foreach ($getUserName as $itemUserName) {

                    $temp = array('value' => $itemUser->user_id, 'text' => $itemUserName->tacGia);
                    array_push($arrUser, $temp);
                }

            }

            return response()->json($arrUser, 200);
        }

    }

    public function getInfoTinhBaoGiaOfUser(Request $request)
    {
        if ($request->check === 0) {
            $getTinh = DB::table('material_cost_for_guests')->where('user_id', $request->idUserImport)->select('tinh')->distinct()->get();
            $arrTinh = [];
            foreach ($getTinh as $itemTinh) {
                $getNameTinh = DB::table('province_cities')->where('symbol_province', $itemTinh->tinh)->first();
                $temp = array('value' => $itemTinh->tinh, 'text' => $getNameTinh->name_province);
                array_push($arrTinh, $temp);
            }

            return response()->json(['tinh' => $arrTinh,
            ], 200);

        } else {
            $getTinh = DB::table('material_costs')->where('user_id', $request->idUserImport)->select('tinh')->distinct()->get();
            $arrTinh = [];
            foreach ($getTinh as $itemTinh) {
                $getNameTinh = DB::table('province_cities')->where('symbol_province', $itemTinh->tinh)->first();
                $temp = array('value' => $itemTinh->tinh, 'text' => $getNameTinh->name_province);
                array_push($arrTinh, $temp);
            }

            return response()->json(['tinh' => $arrTinh,
            ], 200);
        }
    }
// ham lay khu vuc theo tinh
    public function getInfoBaoGiaOfUser(Request $request)
    {
            
        if ($request->check === 0) {
            $arrKhuVuc = [];
            $arrThoiDiem = [];
            $arrKhuVucAndThoiDiem = []; // mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi

            // ($getTinh as $itemTinh) {
            $getRecordOfTinh = DB::table('material_cost_for_guests')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->get();
            $getTemp = DB::table('material_cost_for_guests')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->first();
            $countString = substr_count($getTemp->giaVatTu, ';');
            array_push($arrKhuVucAndThoiDiem, $getTemp->giaVatTu);
            foreach ($getRecordOfTinh as $item) {
                $countStrItem = substr_count($item->giaVatTu, ';');
                if ($countString !== $countStrItem) {
                    array_push($arrKhuVucAndThoiDiem, $item->giaVatTu);
                }
            }

            //}

            foreach ($arrKhuVucAndThoiDiem as $itemKvTd) {
                $arrTempString = explode(';', $itemKvTd);
                foreach ($arrTempString as $itemArr) {
                    $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                    $str1 = substr($itemArr, 0, $pos);
                    $arrTempKvTd = explode(',', $str1);
                    array_push($arrKhuVuc, ['value' => $arrTempKvTd[1], 'text' => $arrTempKvTd[1]]);

                }
            }
            $arrThoiDiem = array_unique($arrThoiDiem, SORT_REGULAR);
            $arrKhuVuc = array_unique($arrKhuVuc, SORT_REGULAR);
            return response()->json([
                'thoidiem' => $arrThoiDiem,
                'khuvuc' => $arrKhuVuc], 200);
        } else {
            $arrKhuVuc = [];
            $arrThoiDiem = [];
            $arrKhuVucAndThoiDiem = []; // mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi

            // ($getTinh as $itemTinh) {
            $getRecordOfTinh = DB::table('material_costs')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->get();
            $getTemp = DB::table('material_costs')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->first();
            $countString = substr_count($getTemp->giaVatTu, ';');
            array_push($arrKhuVucAndThoiDiem, $getTemp->giaVatTu);
            foreach ($getRecordOfTinh as $item) {
                $countStrItem = substr_count($item->giaVatTu, ';');
                if ($countString !== $countStrItem) {
                    array_push($arrKhuVucAndThoiDiem, $item->giaVatTu);
                }
            }

            //}

            foreach ($arrKhuVucAndThoiDiem as $itemKvTd) {
                $arrTempString = explode(';', $itemKvTd);
                foreach ($arrTempString as $itemArr) {
                    $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                    $str1 = substr($itemArr, 0, $pos);
                    $arrTempKvTd = explode(',', $str1);
                    array_push($arrKhuVuc, ['value' => $arrTempKvTd[1], 'text' => $arrTempKvTd[1]]);

                }
            }
            $arrKhuVuc = array_unique($arrKhuVuc, SORT_REGULAR);
            return response()->json([
                'khuvuc' => $arrKhuVuc], 200);
        }
    }

    public function getThoiDiemBaoGiaOfUser(Request $request)
    {
        if ($request->check === 0) {
            $arrKhuVuc = [];
            $arrThoiDiem = [];
            $arrKhuVucAndThoiDiem = []; // mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi

            // ($getTinh as $itemTinh) {
            $getRecordOfTinh = DB::table('material_cost_for_guests')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->get();
            $getTemp = DB::table('material_cost_for_guests')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->first();
            $countString = substr_count($getTemp->giaVatTu, ';');
            array_push($arrKhuVucAndThoiDiem, $getTemp->giaVatTu);
            foreach ($getRecordOfTinh as $item) {
                $countStrItem = substr_count($item->giaVatTu, ';');
                if ($countString !== $countStrItem) {
                    array_push($arrKhuVucAndThoiDiem, $item->giaVatTu);
                }
            }

            //}

            foreach ($arrKhuVucAndThoiDiem as $itemKvTd) {
                $arrTempString = explode(';', $itemKvTd);
                foreach ($arrTempString as $itemArr) {
                    $posKV = strpos($itemArr, $request->khuvuc); // tách giá đến vị trí :
                    if($posKV !==false) {
                        $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                        $str1 = substr($itemArr, 0, $pos);
                        $arrTempKvTd = explode(',', $str1);
                        array_push($arrThoiDiem, ['value' => $arrTempKvTd[0], 'text' => $arrTempKvTd[0]]);

                    }

                }
            }
            $arrThoiDiem = array_unique($arrThoiDiem, SORT_REGULAR);
            return response()->json([
                'thoidiem' => $arrThoiDiem
                ], 200);
        } else {
            $arrKhuVuc = [];
            $arrThoiDiem = [];
            $arrKhuVucAndThoiDiem = []; // mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi

            // ($getTinh as $itemTinh) {
            $getRecordOfTinh = DB::table('material_costs')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->get();
            $getTemp = DB::table('material_costs')
                ->where('tinh', $request->tinh)
                ->where('user_id', $request->idUserImport)
                ->first();
            $countString = substr_count($getTemp->giaVatTu, ';');
            array_push($arrKhuVucAndThoiDiem, $getTemp->giaVatTu);
            foreach ($getRecordOfTinh as $item) {
                $countStrItem = substr_count($item->giaVatTu, ';');
                if ($countString !== $countStrItem) {
                    array_push($arrKhuVucAndThoiDiem, $item->giaVatTu);
                }
            }

            //}

            foreach ($arrKhuVucAndThoiDiem as $itemKvTd) {
                $arrTempString = explode(';', $itemKvTd);
                foreach ($arrTempString as $itemArr) {
                    $posKV = strpos($itemArr, $request->khuvuc); // tách giá đến vị trí :
                    if($posKV !==false) {
                        $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                        $str1 = substr($itemArr, 0, $pos);
                        $arrTempKvTd = explode(',', $str1);
                        array_push($arrThoiDiem, ['value' => $arrTempKvTd[0], 'text' => $arrTempKvTd[0]]);

                    }

                }
            }
            $arrThoiDiem = array_unique($arrThoiDiem, SORT_REGULAR);
            return response()->json([
                'thoidiem' => $arrThoiDiem], 200);
        }
    }


    public function viewBaoGiaWithSelecttion($user_id, $tinh, $khuvuc, $thoidiem, $check, $idUserView,$agreebuy)
    {
        if ($check === '0') {

            $getBaoGia = DB::table('material_cost_for_guests')->where('user_id', $user_id)
                ->where('tinh', $tinh)
                ->get();
            $arrRecordBG = [];
            $gia = '';
            $strKvTd = $thoidiem . ',' . $khuvuc;
            foreach ($getBaoGia as $item) {
                $giaVatTu = $item->giaVatTu;
                $pos = strpos($giaVatTu, $strKvTd);

                if ($pos !== false) { //tim thay gia im port trong gia da co
                    $arrgiaVatTu = explode(';', $giaVatTu);
                    for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                        if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                            $gia = str_replace($strKvTd . ':', '', $arrgiaVatTu[$key]);
                            break;
                        }
                    }

                    $getNameTinh = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
                    array_push($arrRecordBG, [
                        'id' => $item->id,
                        'maVatTu' => $item->maVatTu,
                        'tenVatTu' => $item->tenVatTu,
                        'donVi' => $item->donVi,
                        'nguon' => $item->nguon,
                        'ghiChu' => $item->ghiChu,
                        'tinh' => $getNameTinh->name_province,
                        'tacGia' => $item->tacGia,
                        'giaVatTu' => $gia,
                        'khuVuc' => $khuvuc,
                        'thoiDiem' => $thoidiem,
                    ]);
                }
            }

            $collection = collect($arrRecordBG);
            //return $this->paginateCollection($collection,2);
            $pages = $collection->paginate(20);
            return response()->json(['pagi' => $pages,
            'arrRs' => $arrRecordBG,
            ], 200);

        } else if ($check === '2') { // lấy toàn bộ cac bản giá đã up của user hiện đang login trong bảng material_cost_for_guests

            $getBaoGia = DB::table('material_cost_for_guests')
                ->where('user_id', $user_id)
                ->where('tinh', $tinh)
                ->get();
            $arrKhuVucAndThoiDiem = []; // mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi

            $getTemp = DB::table('material_cost_for_guests')
                ->where('tinh', $tinh)
                ->where('user_id', $user_id)
                ->first();
            $countString = substr_count($getTemp->giaVatTu, ';');
            $arrTemp = explode(';', $getTemp->giaVatTu);
            foreach ($arrTemp as $item) {
                $arrTemp1 = explode(':', $item);
                array_push($arrKhuVucAndThoiDiem, $arrTemp1[0]);

            }
            array_push($arrKhuVucAndThoiDiem, $getTemp->giaVatTu);
            foreach ($getBaoGia as $item) {
                $countStrItem = substr_count($item->giaVatTu, ';');
                if ($countString !== $countStrItem) {
                    $arrTemp = explode(';', $item->giaVatTu);
                    foreach ($arrTemp as $item) {
                        $arrTemp1 = explode(':', $item);
                        array_push($arrKhuVucAndThoiDiem, $arrTemp1[0]);
                    }
                }
            }
            $arrKhuVucAndThoiDiem = array_unique($arrKhuVucAndThoiDiem, SORT_REGULAR); // loại bỏ trùng lặp

            $arrRecordBG = [];
            $gia = '';
            $strKvTd = $thoidiem . ',' . $khuvuc;
            foreach ($arrKhuVucAndThoiDiem as $itemArrKhuVucAndThoiDiem) {
                foreach ($getBaoGia as $item) {
                    $giaVatTu = $item->giaVatTu;
                    $pos = strpos($giaVatTu, $itemArrKhuVucAndThoiDiem);

                    if ($pos !== false) { //tim thay gia im port trong gia da co
                        $arrgiaVatTu = explode(';', $giaVatTu);
                        for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                            if (strpos($arrgiaVatTu[$key], $itemArrKhuVucAndThoiDiem) !== false) {
                                $gia = str_replace($itemArrKhuVucAndThoiDiem . ':', '', $arrgiaVatTu[$key]);
                                break;
                            }
                        }
                        $getNameTinh = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
                        array_push($arrRecordBG, [
                            'id' => $item->id,
                            'maVatTu' => $item->maVatTu,
                            'tenVatTu' => $item->tenVatTu,
                            'donVi' => $item->donVi,
                            'nguon' => $item->nguon,
                            'ghiChu' => $item->ghiChu,
                            'tinh' => $getNameTinh->name_province,
                            'tacGia' => $item->tacGia,
                            'giaVatTu' => $gia,
                            'khuVuc' => $khuvuc,
                            'thoiDiem' => $thoidiem,
                        ]);
                    }
                }
            }
            $collection = collect($arrRecordBG);
            //return $this->paginateCollection($collection,2);
            $pages = $collection->paginate(20);
            return $pages;
            //return response()->json($arrRecordBG,200);
        } else if ($check === '1') {
            $markRs = '';
            $voteRs = '';
            if ($agreebuy === "0") {
                $get = DB::table('user_buy_material_costs')
                ->where('id_user_post', $user_id)
                ->where('tinh', $tinh)
                ->first();
                $strKvTd = $thoidiem . ',' . $khuvuc;
                $isBuy = false;
                $isVote = false;
                if ($get) {
                    $strIdUserBought = $get->id_user_bought;
                    $pos = strpos($strIdUserBought, $strKvTd.':'.$idUserView);
                    if ($pos !== false) {//da mua
                        $isBuy = true;
                    }else  { //chua mua
                        $isBuy = false;
                    }

                    $strIdUserVote = $get->describe_cost;
                    $pos = strpos($strIdUserVote, $strKvTd.':'.$idUserView);
                    if ($pos !== false) {//da mua
                        $isVote = true;
                    }else  { //chua mua
                        $isVote = false;
                    }
                }
                $user = User::find($idUserView);
                $getBaoGia = DB::table('material_costs')->where('user_id', $user_id)
                    ->where('tinh', $tinh)
                    ->get();
                $arrRecordBG = [];
                $gia = '';
                
                $check = false;
                foreach ($getBaoGia as $item) {
                    $giaVatTu = $item->giaVatTu;
                    $vote_mark = $item->vote_mark;
                    $pos = strpos($giaVatTu, $strKvTd);
    
                    if ($pos !== false) { //tim thay gia im port trong gia da co
                        $arrgiaVatTu = explode(';', $giaVatTu);
                        for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                            if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                                $gia = str_replace($strKvTd . ':', '', $arrgiaVatTu[$key]);
                                break;
                            }
                        }
                        if ($check === false) {// lay ra so diem cua bao gia
                            $arrmark = explode(';', $vote_mark);
                            for ($key = 0; $key < count($arrmark); $key++) {
                                if (strpos($arrmark[$key], $strKvTd) !== false) {
                                    $mark = str_replace($strKvTd . ',vote:', '', $arrmark[$key]);
                                    $pos = strpos($mark, ':'); // tách giá đến vị trí :
                                    $markRs = substr($mark, $pos + 1, strlen($mark) - $pos);
                                    $pos = strpos($mark, '|'); // tách giá đến vị trí :
                                    $voteRs = substr($mark, 0, $pos);
                                    $check = true;
                                    break;
                                }
                            }
    
                        }
    
                        $getNameTinh = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
                        array_push($arrRecordBG, [
                            'id' => $item->id,
                            'maVatTu' => $item->maVatTu,
                            'tenVatTu' => $item->tenVatTu,
                            'donVi' => $item->donVi,
                            'nguon' => $item->nguon,
                            'ghiChu' => $item->ghiChu,
                            'tinh' => $getNameTinh->name_province,
                            'tacGia' => $item->tacGia,
                            'giaVatTu' => $gia,
                            'khuVuc' => $khuvuc,
                            'thoiDiem' => $thoidiem,
                        ]);
                    }
                }
    
                $collection = collect($arrRecordBG);
                //return $this->paginateCollection($collection,2);
                $pages = $collection->paginate(20);
                return response()->json(['pagi' => $pages,
                    'markuserview' => $user->total_mark,
                    'isbuy' => $isBuy,
                    'isvote' => $isVote,
                    'votecur' => $voteRs,
                    'arrRs' => $arrRecordBG,
                    'mark' => $markRs], 200);
                //return response()->json($arrRecordBG,200);
            }else{
                // DANH DAU LA DA MUA BAO GIA NAY
                $get = DB::table('user_buy_material_costs')
                ->where('id_user_post', $user_id)
                ->where('tinh', $tinh)
                ->first();
                $strKvTd = $thoidiem . ',' . $khuvuc;
                if($get){

                    $strIdUserBought = $get->id_user_bought;
                    if($strIdUserBought){
                        $strIdUserBoughtUpdate = $strIdUserBought.';'.$strKvTd.':'.$idUserView;
                    }else{
                        $strIdUserBoughtUpdate = $idUserView;
                    }
                    DB::table('user_buy_material_costs')
                        ->where('id_user_post', $user_id)
                        ->where('tinh', $tinh)
                        ->update([
                            'id_user_bought' => $strIdUserBoughtUpdate,
                            
                        ]);
                }else {
                    DB::table('user_buy_material_costs')
                    ->insert([
                        'id_user_buy'=>null,
                        'id_user_post' => $idUserView,
                        'tinh' => $tinh,
                        'id_user_bought' => $strKvTd.':'.$user_id,
                    ]);
                }
//====== TRU DIEM NGUOI MUA CONG DIEM CHO NGUOI BAN========//
                $getBaoGia = DB::table('material_costs')->where('user_id', $user_id)
                ->where('tinh', $tinh)
                ->get();
                $arrRecordBG = [];
                $gia = '';
                
                $check = false;
                foreach ($getBaoGia as $item) {
                    $giaVatTu = $item->giaVatTu;
                    $vote_mark = $item->vote_mark;
                    $pos = strpos($giaVatTu, $strKvTd);
    
                    if ($pos !== false) { //tim thay gia im port trong gia da co
                       
                        if ($check === false) {// lay ra so diem cua bao gia
                            $arrmark = explode(';', $vote_mark);
                            for ($key = 0; $key < count($arrmark); $key++) {
                                if (strpos($arrmark[$key], $strKvTd) !== false) {
                                    $mark = str_replace($strKvTd . ',vote:', '', $arrmark[$key]);
                                    $pos = strpos($mark, ':'); // tách giá đến vị trí :
                                    $markRs = substr($mark, $pos + 1, strlen($mark) - $pos);
                                    $check = true;
                                    break;
                                }
                            }
    
                        }
                    }
                    if($check === true) {
                        break;
                    }
                }
                $userSale = User::find($user_id);
                $totalMarkUpdate = $userSale->total_mark + (int)$markRs;
                $userSale->total_mark = $totalMarkUpdate;
                $userSale->save();


                $userBuy = User::find($idUserView);
                $totalMarkUpdate = $userBuy->total_mark - (int)$markRs;
                $userBuy->total_mark = $totalMarkUpdate;
                $userBuy->save();

                return response()->json([
                'mark' => $totalMarkUpdate], 200);
            }
        } else if($check === '3') {// truong hop nay danh cho khi click vao phan trang
            $user = User::find($idUserView);
            $getBaoGia = DB::table('material_costs')->where('user_id', $user_id)
                ->where('tinh', $tinh)
                ->get();
            $arrRecordBG = [];
            $gia = '';
            $strKvTd = $thoidiem . ',' . $khuvuc;
            $check = false;
            foreach ($getBaoGia as $item) {
                $giaVatTu = $item->giaVatTu;
                $vote_mark = $item->vote_mark;
                $pos = strpos($giaVatTu, $strKvTd);

                if ($pos !== false) { //tim thay gia im port trong gia da co
                    $arrgiaVatTu = explode(';', $giaVatTu);
                    for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                        if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                            $gia = str_replace($strKvTd . ':', '', $arrgiaVatTu[$key]);
                            break;
                        }
                    }
                    if ($check === false) {// lay ra so diem cua bao gia
                        $arrmark = explode(';', $vote_mark);
                        for ($key = 0; $key < count($arrmark); $key++) {
                            if (strpos($arrmark[$key], $strKvTd) !== false) {
                                $mark = str_replace($strKvTd . ',vote:', '', $arrmark[$key]);
                                $pos = strpos($mark, ':'); // tách giá đến vị trí :
                                $markRs = substr($mark, $pos + 1, strlen($mark) - $pos);
                                $pos = strpos($mark, '|'); // tách giá đến vị trí :
                                $voteRs = substr($mark, 0, $pos);
                                $check = true;
                                break;
                            }
                        }

                    }

                    $getNameTinh = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
                    array_push($arrRecordBG, [
                        'id' => $item->id,
                        'maVatTu' => $item->maVatTu,
                        'tenVatTu' => $item->tenVatTu,
                        'donVi' => $item->donVi,
                        'nguon' => $item->nguon,
                        'ghiChu' => $item->ghiChu,
                        'tinh' => $getNameTinh->name_province,
                        'tacGia' => $item->tacGia,
                        'giaVatTu' => $gia,
                        'khuVuc' => $khuvuc,
                        'thoiDiem' => $thoidiem,
                    ]);
                }
            }

            $collection = collect($arrRecordBG);
            //return $this->paginateCollection($collection,2);
            $pages = $collection->paginate(20);
            return response()->json(['pagi' => $pages,
                'markuserview' => $user->total_mark,
                'votecur' => $voteRs,
                'mark' => $markRs], 200);
            //return response()->json($arrRecordBG,200);
        }

    }

    public function BaoGiaWithSelecttionForSearchApprove(Request $request)
    {
        $getBaoGia = DB::table('material_cost_for_guests')
            ->where('user_id', $request->user_id)
            ->where('tinh', $request->tinh)
            ->get();
        $arrRecordBG = [];
        $gia = '';
        $strKvTd = $request->thoidiem . ',' . $request->khuvuc;
        foreach ($getBaoGia as $item) {
            $giaVatTu = $item->giaVatTu;
            $pos = strpos($giaVatTu, $strKvTd);

            if ($pos !== false) { //tim thay gia im port trong gia da co
                $arrgiaVatTu = explode(';', $giaVatTu);
                for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                    if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                        $gia = str_replace($strKvTd . ':', '', $arrgiaVatTu[$key]);
                        break;
                    }
                }

            }
            $getNameTinh = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
            array_push($arrRecordBG, [
                'id' => $item->id,
                'maVatTu' => $item->maVatTu,
                'tenVatTu' => $item->tenVatTu,
                'donVi' => $item->donVi,
                'nguon' => $item->nguon,
                'ghiChu' => $item->ghiChu,
                'tinh' => $getNameTinh->name_province,
                'tacGia' => $item->tacGia,
                'giaVatTu' => $gia,
                'khuVuc' => $request->khuvuc,
                'thoiDiem' => $request->thoidiem,
            ]);
        }

        return $arrRecordBG;
        //return response()->json($arrRecordBG,200);
    }

    public function getDataTableGiaVTGuest()
    {
        //$giaVt = giaVatTu::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;
        $giaVt = material_cost_for_guest::paginate(20);
        return response()->json(
            // 'success' => true,
            // 'data' => $giaVt,
            $giaVt
        );
    }

    public function updateDataGiaVatTuUserUp(Request $request, $idBg, $idUser)
    {
        $user = User::find($idUser);
        // $pm = $u->getAllPermissions($u->permissions[0]);
        if ($user->can('edit-gia-vat-tu')) {
            $itemupdate = material_cost_for_guest::find($idBg);
            if (!$itemupdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                ], 400);
            }
            $giaDaCo = $itemupdate->giaVatTu;
            $giaUpDate = $request->thoidiem . ',' . $request->khuvuc;
            $arrGiaDaCo = explode(';', $giaDaCo);
            for ($key = 0; $key < count($arrGiaDaCo); $key++) {
                if (strpos($arrGiaDaCo[$key], $giaUpDate) !== false) {
                    unset($arrGiaDaCo[$key]); //xoa bo phan tu trong mang
                    break;
                }
            }
            array_push($arrGiaDaCo, $giaUpDate . ':' . $request->giaVatTu);
            $giaDaCoUpdate = implode(';', $arrGiaDaCo);
            $updated = DB::table('material_cost_for_guests')
                ->where('id', $idBg)
                ->update([
                    'maVatTu' => $request->maVatTu && $request->maVatTu !== "null" ? $request->maVatTu : null,
                    'tenVatTu' => $request->tenVatTu && $request->tenVatTu !== "null" ? $request->tenVatTu : null,
                    'donVi' => $request->donVi && $request->donVi !== "null" ? $request->donVi : null,
                    'giaVatTu' => $giaDaCoUpdate,
                    'nguon' => $request->nguon && $request->nguon !== "null" ? $request->nguon : null,
                    'ghiChu' => $request->ghiChu && $request->ghiChu !== "null" ? $request->ghiChu : null,

                ]);
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

    public function approve(Request $request, $idUserApprove, $agreeOverride)
    {
        $material_cost = new material_cost();
        $user = User::find($idUserApprove);
        if ($user->can('approve-gia-vat-tu')) {
            $user = User::find($idUserApprove);
            $arrTemp = [];
            $arrUpdate = [];
            DB::beginTransaction(); // đảm bảo tính toàn vẹn dữ liệu
            $arrData = DB::table('material_cost_for_guests')
                ->where('tinh', $request->tinh && $request->tinh !== "null" ? $request->tinh : null)
                ->where('user_id', $request->user_id && $request->user_id !== "null" ? $request->user_id : null)
                ->get();

            foreach ($arrData as $item) {
                $giaDaCo = $item->giaVatTu;
                $giaDaCoUpdate = '';
                $pos1 = strpos($giaDaCo, $request->giaVt); // chưa vị trí tìm đc trong gia đã có
                if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                    $arrgiaDaCo = explode(';', $giaDaCo);
                    for ($key = 0; $key < count($arrgiaDaCo); $key++) {
                        if (strpos($arrgiaDaCo[$key], $request->giaVt) === false) {
                            unset($arrgiaDaCo[$key]); //xoa bo phan tu khong chua $request->giaVt
                        }
                    }
                    $giaDaCoUpdate = implode(';', $arrgiaDaCo);
                }
                $item->giaVatTu = $giaDaCoUpdate;
            }
            //$arrData = json_decode($request->,true);// dungf cachs nay thi $arrData se la mang con neu khong co true thi se la object
            $exitsPrice = false;
            $arrDupplicate = []; // mảng chứa các công việc có 6 tiêu chí của $get trùng nhau nhưng chỉ lấy 1 lần thôi
            $arrCheck = []; // mảng xác nhận rằng đã update 1 lần của $get. mảng này sẽ có số phần tử bằng với $arrDupplicate sau khi đã check

            try {
                foreach ($arrData as $item) {
                    $count = count(get_object_vars($item)); // dung ham nay de dem so luong cua 1 stdclass object sau khi decode
                    //giaVatTu::create([
                    if ($count >= 8) { // điều kiện bắt trường hợp bảng giá thiếu 1 trong các cột giá , mã hay nguồn thì sẽ lỗi. đây là trường hợp dòng chỉ có 2 - 3 ô

                        $get = DB::table('material_costs')
                            ->where('maVatTu', $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null)
                            ->where('tenVatTu', $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null)
                            ->where('donVi', $item->donVi && $item->donVi !== "null" ? $item->donVi : null)
                        //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
                            ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                            ->where('ghiChu', $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null)
                            ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                            ->where('user_id', $item->user_id && $item->user_id !== "null" ? $item->user_id : null)
                            ->get();
                        // chú ý phuong thức get trả về 1 colection chứ không phải là 1 mảng nên kiểu dữ liệu của $get sẽ không phải mảng
                        //    if($get->isEmpty()) {
                        //       echo('empty($get)');
                        //       echo(gettype($get));
                        //       echo($get->isEmpty());

                        //    }
                        if (count($get) > 1) { //sét trường hợp mà $get có nhiều loại vật tư trùng nhau thì chỉ cho update giá 1 làn khi duyết qua $get 1 vòng
                            // còn các vòng sau thì khong update giá nữa không thì không update giá đc. ví dụ có 3 công việc 1,2,3 giống hệt nhau trong bảng vật tư
                            // khi đó khi lấy công việc 1 thì $get có 3 phần tử. khi lặp đến công việc 2 thì $get cũng có 3 nên ta chỉ cho update giá khi lặp $get của
                            // công việc 1 thôi
                            $exit = false;
                            foreach ($get as $getItem) {
                                if (count($arrDupplicate) > 0) {
                                    foreach ($arrDupplicate as $arrDupplicateItem) {

                                        if (($arrDupplicateItem->maVatTu && $arrDupplicateItem->maVatTu !== "null" ? $arrDupplicateItem->maVatTu : null) == ($getItem->maVatTu && $getItem->maVatTu !== "null" ? $getItem->maVatTu : null)
                                            && ($arrDupplicateItem->tenVatTu && $arrDupplicateItem->tenVatTu !== "null" ? $arrDupplicateItem->tenVatTu : null) == ($getItem->tenVatTu && $getItem->tenVatTu !== "null" ? $getItem->tenVatTu : null)
                                            && ($arrDupplicateItem->donVi && $arrDupplicateItem->donVi !== "null" ? $arrDupplicateItem->donVi : null) == ($getItem->donVi && $getItem->donVi !== "null" ? $getItem->donVi : null)
                                            && ($arrDupplicateItem->nguon && $arrDupplicateItem->nguon !== "null" ? $arrDupplicateItem->nguon : null) == ($getItem->nguon && $getItem->nguon !== "null" ? $getItem->nguon : null)
                                            && ($arrDupplicateItem->ghiChu && $arrDupplicateItem->ghiChu !== "null" ? $arrDupplicateItem->ghiChu : null) == ($getItem->ghiChu && $getItem->ghiChu !== "null" ? $getItem->ghiChu : null)
                                            && ($arrDupplicateItem->tinh && $arrDupplicateItem->tinh !== "null" ? $arrDupplicateItem->tinh : null) == ($getItem->tinh && $getItem->tinh !== "null" ? $getItem->tinh : null)) {
                                            $exit = true;
                                            break;
                                        }
                                    }
                                    if ($exit == false) {

                                        array_push($arrDupplicate, $getItem);
                                        $exit = true;
                                        break;

                                    }
                                } else {
                                    array_push($arrDupplicate, $getItem);
                                    break;
                                }
                                if ($exit == true) {
                                    break;
                                }
                            }
                        }
                        if ($get->isEmpty()) { // không tìm thấy bản ghi nào trùng

                            array_push($arrTemp, [
                                'maVatTu' => $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null,
                                'tenVatTu' => $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null,
                                'donVi' => $item->donVi && $item->donVi !== "null" ? $item->donVi : null,
                                'giaVatTu' => $item->giaVatTu && $item->giaVatTu !== "null" ? $item->giaVatTu : null,
                                'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                'ghiChu' => $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null,
                                'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                'tacGia' => $item->tacGia && $item->tacGia !== "null" ? $item->tacGia : null,
                                'user_id' => $item->user_id && $item->user_id !== "null" ? $item->user_id : null,
                                'vote_mark' => $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null,
                                'created_at' => $material_cost->freshTimestamp(),
                                'updated_at' => $material_cost->freshTimestamp(),
                            ]);
                        } else { // truong họp khong trung
                            if (count($get) == 1) {
                                foreach ($get as $getItem) {
                                    $giaDaCo = $getItem->giaVatTu;
                                    $giaImport = $item->giaVatTu && $item->giaVatTu !== "null" ? $item->giaVatTu : null;
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
                                                'maVatTu' => $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null,
                                                'tenVatTu' => $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null,
                                                'donVi' => $item->donVi && $item->donVi !== "null" ? $item->donVi : null,
                                                'giaVatTu' => $giaAfterUpdate,
                                                'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                                'ghiChu' => $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null,
                                                'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                                'tacGia' => $item->tacGia && $item->tacGia !== "null" ? $item->tacGia : null,
                                                'user_id' => $item->user_id && $item->user_id !== "null" ? $item->user_id : null,
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

                            if (count($get) > 1 && (count($arrDupplicate) !== count($arrCheck))) { // trường hợp khi chưa update thì $arrDupplicate và arrcheck sẽ có số phần tử k bằng nhau
                                foreach ($get as $getItem) {
                                    $giaDaCo = $getItem->giaVatTu;
                                    $giaImport = $item->giaVatTu && $item->giaVatTu !== "null" ? $item->giaVatTu : null;
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
                                                'maVatTu' => $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null,
                                                'tenVatTu' => $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null,
                                                'donVi' => $item->donVi && $item->donVi !== "null" ? $item->donVi : null,
                                                'giaVatTu' => $giaAfterUpdate,
                                                'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                                'ghiChu' => $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null,
                                                'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                                'tacGia' => $item->tacGia && $item->tacGia !== "null" ? $item->tacGia : null,
                                                'user_id' => $item->user_id && $item->user_id !== "null" ? $item->user_id : null,
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
                                array_push($arrCheck, 'okcheck');
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
                                ->where('maVatTu', $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null)
                                ->where('tenVatTu', $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null)
                                ->where('donVi', $item->donVi && $item->donVi !== "null" ? $item->donVi : null)
                            //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
                                ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
                                ->where('ghiChu', $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null)
                                ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
                                ->where('user_id', $item->user_id && $item->user_id !== "null" ? $item->user_id : null)
                                ->get();

                            foreach ($get as $getItem) {
                                $giaDaCo = $getItem->giaVatTu;
                                $giaImport = $item->giaVatTu && $item->giaVatTu !== "null" ? $item->giaVatTu : null;
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
                                            'maVatTu' => $item->maVatTu && $item->maVatTu !== "null" ? $item->maVatTu : null,
                                            'tenVatTu' => $item->tenVatTu && $item->tenVatTu !== "null" ? $item->tenVatTu : null,
                                            'donVi' => $item->donVi && $item->donVi !== "null" ? $item->donVi : null,
                                            'giaVatTu' => $giaDaCoUpdate,
                                            'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
                                            'ghiChu' => $item->ghiChu && $item->ghiChu !== "null" ? $item->ghiChu : null,
                                            'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
                                            'tacGia' => $item->tacGia && $item->tacGia !== "null" ? $item->tacGia : null,
                                            'user_id' => $item->user_id && $item->user_id !== "null" ? $item->user_id : null,
                                            'vote_mark' => $voteDaCoUpdate,
                                            'updated_at' => $material_cost->freshTimestamp(),
                                        ]);

                                }
                            }
                        }

                        $getRecoreDel = DB::table('material_cost_for_guests')
                            ->where('tinh', $request->tinh && $request->tinh !== "null" ? $request->tinh : null)
                            ->where('user_id', $request->user_id && $request->user_id !== "null" ? $request->user_id : null)
                            ->get();
                            foreach ($getRecoreDel as $getItem) {
                                // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                                    $giaDaCo = $getItem->giaVatTu;
                                    $arrgiaDaCo = explode(';', $giaDaCo);
                                    for ($key = 0; $key < count($arrgiaDaCo); $key++) {
                                        if (strpos($arrgiaDaCo[$key], $request->giaVt) !== false) {
                                            unset($arrgiaDaCo[$key]); //xoa bo phan tu trong mang
                                            break;
                                        }
                                    }
                                    if(count($arrgiaDaCo) == 0) {
                                        DB::table('material_cost_for_guests')
                                        ->where('id', $getItem->id)
                                        ->delete();
                                    }else if(count($arrgiaDaCo) > 0) {
                                        $giaDaCoUpdate = implode(';', $arrgiaDaCo);
                                        DB::table('material_cost_for_guests')
                                        ->where('id', $getItem->id)
                                        ->update(['giaVatTu'=> $giaDaCoUpdate]);

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
                    $arrTemp = [];
                    $arrUpdate = [];
                    $getRecoreDel = DB::table('material_cost_for_guests')
                    ->where('tinh', $request->tinh && $request->tinh !== "null" ? $request->tinh : null)
                    ->where('user_id', $request->user_id && $request->user_id !== "null" ? $request->user_id : null)
                    ->get();
                    foreach ($getRecoreDel as $getItem) {
                        // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                            $giaDaCo = $getItem->giaVatTu;
                            $arrgiaDaCo = explode(';', $giaDaCo);
                            for ($key = 0; $key < count($arrgiaDaCo); $key++) {
                                if (strpos($arrgiaDaCo[$key], $request->giaVt) !== false) {
                                    unset($arrgiaDaCo[$key]); //xoa bo phan tu trong mang
                                    break;
                                }
                            }
                            if(count($arrgiaDaCo) == 0) {
                                DB::table('material_cost_for_guests')
                                ->where('id', $getItem->id)
                                ->delete();
                            }else if(count($arrgiaDaCo) > 0) {
                                $giaDaCoUpdate = implode(';', $arrgiaDaCo);
                                DB::table('material_cost_for_guests')
                                ->where('id', $getItem->id)
                                ->update(['giaVatTu'=> $giaDaCoUpdate]);

                            }
                    }
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

    public function deleteBaoGia(Request $request) {
        DB::beginTransaction();
        try {
        $getRecoreDel = DB::table('material_cost_for_guests')
        ->where('tinh', $request->tinh && $request->tinh !== "null" ? $request->tinh : null)
        ->where('user_id', $request->user_id && $request->user_id !== "null" ? $request->user_id : null)
        ->get();
        foreach ($getRecoreDel as $getItem) {
            // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
                $giaDaCo = $getItem->giaVatTu;
                $arrgiaDaCo = explode(';', $giaDaCo);
                for ($key = 0; $key < count($arrgiaDaCo); $key++) {
                    if (strpos($arrgiaDaCo[$key], $request->giaVt) !== false) {
                        unset($arrgiaDaCo[$key]); //xoa bo phan tu trong mang
                        break;
                    }
                }
                if(count($arrgiaDaCo) == 0) {
                    DB::table('material_cost_for_guests')
                    ->where('id', $getItem->id)
                    ->delete();
                }else if(count($arrgiaDaCo) > 0) {
                    $giaDaCoUpdate = implode(';', $arrgiaDaCo);
                    DB::table('material_cost_for_guests')
                    ->where('id', $getItem->id)
                    ->update(['giaVatTu'=> $giaDaCoUpdate]);

                }
        }
        DB::commit();
        return response()->json([
            'success' => true,
            'msg' => 'Hoàn tất xóa giá vật tư',
        ]);
    } catch (Exception $exception) {
        DB::rollBack();
        $this->reportException($exception);

        //$response = $this->renderException($request, $exception);

    }
    } 

    public function handleLike(Request $request)
    {
        $get = DB::table('user_buy_material_costs')
            ->where('id_user_post', $request->user_id)
            ->where('tinh', $request->tinh)
            ->get();
        $strKvTd = $request->thoidiem . ',' . $request->khuvuc;
        if ($get->isEmpty()) {
            DB::table('user_buy_material_costs')
                ->insert([
                    'id_user_buy' => $request->idUserView,
                        'id_user_post' => $request->user_id,
                        'tinh' => $request->tinh,
                        'describe_cost' => $strKvTd.':'.$request->idUserView,
                    

                ]);
                $strUserBuy = $request->idUserView;
                $intVote = 0;
                $getUpdate = DB::table('material_costs')
                    ->where('tinh', $request->tinh)
                    ->where('user_id', $request->user_id)
                    ->get();

                foreach ($getUpdate as $getItem) {
                    $voteDaCo = $getItem->vote_mark;

                    $pos1 = strpos($voteDaCo, $strKvTd); // chưa vị trí tìm đc trong gia đã có
                    if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)

                        $arrvoteDaCo = explode(';', $voteDaCo);
                        for ($key = 0; $key < count($arrvoteDaCo); $key++) {
                            if (strpos($arrvoteDaCo[$key], $strKvTd) !== false) {
                                $vote = str_replace($strKvTd . ',vote:', '', $arrvoteDaCo[$key]);
                                $pos = strpos($vote, '|'); // tách giá đến vị trí :
                                $mark = substr($vote, $pos + 1, strlen($vote) - $pos);
                                $voteRs = substr($vote, 0, $pos);
                                $intVote = (int) $voteRs + 1;
                                $arrvoteDaCo[$key] = $strKvTd . ',vote:' . $intVote .'|'. $mark;
                                break;
                            }
                        }
                        $voteDaCoUpdate = implode(';', $arrvoteDaCo);
                        DB::table('material_costs')
                            ->where('id', $getItem->id)
                            ->update([
                                'vote_mark' => $voteDaCoUpdate,
                            ]);

                    }
                }
                return $intVote;
        } 
        else {
            $itemUpdate = DB::table('user_buy_material_costs')
                ->where('id_user_post', $request->user_id)
                ->where('tinh', $request->tinh)
                ->first();
            $strUserBuy = $itemUpdate->id_user_buy;
            $strdescribeCost = $itemUpdate->describe_cost;
            $pos = strpos($strUserBuy, (string) $request->idUserView); // chưa vị trí tìm đc trong gia đã có
            $pos1 = strpos($strdescribeCost, $strKvTd.':'.$request->idUserView); // chưa vị trí tìm đc trong gia đã có
            $intVote = 0;
            if ($pos !== false && $pos1 !== false) { // người mua đã vote cho bao giá
                $arrStrUserBuy = explode(',', $strUserBuy);
                for ($key = 0; $key < count($arrStrUserBuy); $key++) {
                    if ($arrStrUserBuy[$key] == $request->idUserView) {
                        unset($arrStrUserBuy[$key]); //xoa bo phan tu trong mang
                        break;
                    }
                }
                $newStrUserBuy = implode(',', $arrStrUserBuy);

                $arrstrdescribeCost = explode(';', $strdescribeCost);
                for ($key = 0; $key < count($arrstrdescribeCost); $key++) {
                    if ($arrstrdescribeCost[$key] == $strKvTd.':'.$request->idUserView) {
                        unset($arrstrdescribeCost[$key]); //xoa bo phan tu trong mang
                        break;
                    }
                }
                $newstrdescribeCost = implode(';', $arrstrdescribeCost);
                DB::table('user_buy_material_costs')
                    ->where('id_user_post', $request->user_id)
                    ->where('tinh', $request->tinh)
                    ->update([
                        'id_user_buy' => $newStrUserBuy,
                        'describe_cost' => $newstrdescribeCost,
                    ]);
                $getUpdate = DB::table('material_costs')
                    ->where('tinh', $request->tinh)
                    ->where('user_id', $request->user_id)
                    ->get();

                foreach ($getUpdate as $getItem) {
                    $voteDaCo = $getItem->vote_mark;

                    $pos1 = strpos($voteDaCo, $strKvTd); // chưa vị trí tìm đc trong gia đã có
                    if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)

                        $arrvoteDaCo = explode(';', $voteDaCo);
                        for ($key = 0; $key < count($arrvoteDaCo); $key++) {
                            if (strpos($arrvoteDaCo[$key], $strKvTd) !== false) {
                                $vote = str_replace($strKvTd . ',vote:', '', $arrvoteDaCo[$key]);
                                $pos = strpos($vote, '|'); // tách giá đến vị trí :
                                $mark = substr($vote, $pos + 1, strlen($vote) - $pos);
                                $voteRs = substr($vote, 0, $pos);
                                $intVote = (int) $voteRs - 1;
                                $arrvoteDaCo[$key] = $strKvTd . ',vote:' . $intVote . '|' . $mark;
                                break;
                            }
                        }
                        $voteDaCoUpdate = implode(';', $arrvoteDaCo);
                        DB::table('material_costs')
                            ->where('id', $getItem->id)
                            ->update([
                                'vote_mark' => $voteDaCoUpdate,
                            ]);

                    }
                }
                return $intVote;
            }
            if ($pos === false && $pos1 !== false) { // chưa like
                if($strUserBuy){
                    $newStrUserBuy = $strUserBuy . ',' . $request->idUserView;
                }else {
                    $newStrUserBuy = $request->idUserView;
                }
                if($strdescribeCost){
                    $newstrdescribeCost = $strdescribeCost . ';' . $strKvTd.':'.$request->idUserView;
                }else {
                    $newstrdescribeCost = $strKvTd.':'.$request->idUserView;
                }
                $intVote = 0;
                DB::table('user_buy_material_costs')
                    ->where('id_user_post', $request->user_id)
                    ->where('tinh', $request->tinh)
                    ->update([
                        'id_user_buy' => $newStrUserBuy,
                        'describe_cost' => $newstrdescribeCost,
                    ]);
                $getUpdate = DB::table('material_costs')
                    ->where('tinh', $request->tinh)
                    ->where('user_id', $request->user_id)
                    ->get();

                foreach ($getUpdate as $getItem) {
                    $voteDaCo = $getItem->vote_mark;

                    $pos1 = strpos($voteDaCo, $strKvTd); // chưa vị trí tìm đc trong gia đã có
                    if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)

                        $arrvoteDaCo = explode(';', $voteDaCo);
                        for ($key = 0; $key < count($arrvoteDaCo); $key++) {
                            if (strpos($arrvoteDaCo[$key], $strKvTd) !== false) {
                                $vote = str_replace($strKvTd . ',vote:', '', $arrvoteDaCo[$key]);
                                $pos = strpos($vote, '|'); // tách giá đến vị trí :
                                $mark = substr($vote, $pos + 1, strlen($vote) - $pos);
                                $voteRs = substr($vote, 0, $pos);
                                $intVote = (int) $voteRs + 1;
                                $arrvoteDaCo[$key] = $strKvTd . ',vote:' . $intVote .'|'. $mark;
                                break;
                            }
                        }
                        $voteDaCoUpdate = implode(';', $arrvoteDaCo);
                        DB::table('material_costs')
                            ->where('id', $getItem->id)
                            ->update([
                                'vote_mark' => $voteDaCoUpdate,
                            ]);

                    }
                }
                return $intVote;
            }
            if ($pos === false && $pos1 === false) { // chưa like và cũng chưa có khu vực
                if($strUserBuy) {

                    $newStrUserBuy = $strUserBuy . ',' . $request->idUserView;
                }else {
                   $newStrUserBuy = $request->idUserView;
                }
                if($strdescribeCost){

                    $newStrdescribeCost = $strdescribeCost . ';' . $strKvTd.':'.$request->idUserView;
                }else {
                    $newStrdescribeCost = $strKvTd.':'.$request->idUserView;
                }
                $intVote = 0;
                DB::table('user_buy_material_costs')
                    ->where('id_user_post', $request->user_id)
                    ->where('tinh', $request->tinh)
                    ->update([
                        'id_user_buy' => $newStrUserBuy,
                        'describe_cost' => $newStrdescribeCost,

                    ]);
                $getUpdate = DB::table('material_costs')
                    ->where('tinh', $request->tinh)
                    ->where('user_id', $request->user_id)
                    ->get();

                foreach ($getUpdate as $getItem) {
                    $voteDaCo = $getItem->vote_mark;

                    $pos1 = strpos($voteDaCo, $strKvTd); // chưa vị trí tìm đc trong gia đã có
                    if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)

                        $arrvoteDaCo = explode(';', $voteDaCo);
                        for ($key = 0; $key < count($arrvoteDaCo); $key++) {
                            if (strpos($arrvoteDaCo[$key], $strKvTd) !== false) {
                                $vote = str_replace($strKvTd . ',vote:', '', $arrvoteDaCo[$key]);
                                $pos = strpos($vote, '|'); // tách giá đến vị trí :
                                $mark = substr($vote, $pos + 1, strlen($vote) - $pos);
                                $voteRs = substr($vote, 0, $pos);
                                $intVote = (int) $voteRs + 1;
                                $arrvoteDaCo[$key] = $strKvTd . ',vote:' . $intVote .'|'. $mark;
                                break;
                            }
                        }
                        $voteDaCoUpdate = implode(';', $arrvoteDaCo);
                        DB::table('material_costs')
                            ->where('id', $getItem->id)
                            ->update([
                                'vote_mark' => $voteDaCoUpdate,
                            ]);

                    }
                }
                return $intVote;
            }
        }
    }
}
