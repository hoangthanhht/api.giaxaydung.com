<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\material_cost_for_guest;
use App\Models\material_cost;
use App\Traits\HelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class material_cost_for_guestController extends Controller
{
    use HelperTrait;
    public function store(Request $request, $idUserImport, $agreeOverride)
    {
        $material_cost = new material_cost_for_guest();
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
        } else {
            return response([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện tác vụ này',
            ], 200);
        }
    }
    // hàm lấy ra thông tin của người đăng báo giá. thông tin về khu vực thời điểm....của báo giá
    public function getUserUpBaoGia() {
        $getUserId = DB::table('material_cost_for_guests')->select('user_id')->distinct()->get();
        $arrUser=[];
        foreach ($getUserId as $itemUser) {
            $getUserName = DB::table('material_cost_for_guests')->where('user_id',$itemUser->user_id)->select('tacGia')->distinct()->get();
            foreach ($getUserName as $itemUserName) {

                $temp = array('value'=>$itemUser->user_id,'text'=> $itemUserName->tacGia);
                array_push($arrUser,$temp);
            }
            
        }

        return response()->json($arrUser, 200); 

    }

    public function getInfoTinhBaoGiaOfUser($idUser) {
        $getTinh = DB::table('material_cost_for_guests')->where('user_id',$idUser)->select('tinh')->distinct()->get();
        $arrTinh = [];
        foreach ($getTinh as $itemTinh) {
            $getNameTinh = DB::table('province_cities')->where('symbol_province',$itemTinh->tinh)->first();
            $temp = array('value'=>$itemTinh->tinh,'text'=> $getNameTinh->name_province);
            array_push($arrTinh,$temp);
        }

       
        return response()->json(['tinh'=>$arrTinh,
                                    ], 200); 
    }

    public function getInfoBaoGiaOfUser(Request $request) {
        $arrKhuVuc = [];
        $arrThoiDiem = [];
        $arrKhuVucAndThoiDiem=[];// mảng chứa những bản ghi của tỉnh mà có giá vạt tư khác nhau về khu vực và thời điểm thôi
    

        // ($getTinh as $itemTinh) {
            $getRecordOfTinh = DB::table('material_cost_for_guests')
            ->where('tinh',$request->tinh)
            ->where('user_id',$request->idUserImport)
            ->get();
            $getTemp = DB::table('material_cost_for_guests')
            ->where('tinh',$request->tinh)
            ->where('user_id',$request->idUserImport)
            ->first();
            $countString = substr_count($getTemp->giaVatTu,';');
            array_push($arrKhuVucAndThoiDiem,$getTemp->giaVatTu);
            foreach ($getRecordOfTinh as $item) {
                $countStrItem = substr_count($item->giaVatTu,';');
                if($countString!==$countStrItem) {
                    array_push($arrKhuVucAndThoiDiem,$item->giaVatTu);
                }
            }
           
        //}

        foreach ($arrKhuVucAndThoiDiem as $itemKvTd) {
            $arrTempString = explode(';', $itemKvTd);
            foreach ($arrTempString as $itemArr) {
                $pos = strpos($itemArr, ':'); // tách giá đến vị trí :
                $str1 = substr($itemArr, 0, $pos);
                $arrTempKvTd = explode(',', $str1);
                array_push($arrThoiDiem,['value'=>$arrTempKvTd[0],'text'=>$arrTempKvTd[0]]);
                array_push($arrKhuVuc,['value'=>$arrTempKvTd[1],'text'=>$arrTempKvTd[1]]);
               
            }
        }
        $arrThoiDiem = array_unique($arrThoiDiem, SORT_REGULAR);
        $arrKhuVuc = array_unique($arrKhuVuc, SORT_REGULAR);
        return response()->json([
                                 'thoidiem'=>$arrThoiDiem,
                                 'khuvuc'=>$arrKhuVuc   ], 200); 
    }
    public function viewBaoGiaWithSelecttion($user_id,$tinh,$khuvuc,$thoidiem) {
        $getBaoGia = DB::table('material_cost_for_guests')->where('user_id',$user_id)
        ->where('tinh',$tinh)
        ->get();
        $arrRecordBG = [];
        $gia = '';
        $strKvTd = $thoidiem.','.$khuvuc;
        foreach ($getBaoGia as $item) {
            $giaVatTu = $item->giaVatTu;
            $pos = strpos($giaVatTu, $strKvTd);
    
            if ($pos !== false) { //tim thay gia im port trong gia da co
                $arrgiaVatTu = explode(';', $giaVatTu);
                for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                    if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                        $gia = str_replace($strKvTd.':','',$arrgiaVatTu[$key]);
                        break;
                    }
                }                         
            
            }
            $getNameTinh = DB::table('province_cities')->where('symbol_province',$item->tinh)->first();
            array_push($arrRecordBG,[
                'id'=>$item->id,
                'maVatTu'=>$item->maVatTu,
                'tenVatTu'=>$item->tenVatTu,
                'donVi'=>$item->donVi,
                'nguon'=>$item->nguon,
                'ghiChu'=>$item->ghiChu,
                'tinh'=>$getNameTinh->name_province,
                'tacGia'=>$item->tacGia,
                'giaVatTu'=>$gia,
                'khuVuc'=>$khuvuc,
                'thoiDiem'=>$thoidiem,
            ]);
        }
        
        $collection = collect($arrRecordBG);
    //return $this->paginateCollection($collection,2);
        $pages = $collection->paginate(20);
        return $pages;
        //return response()->json($arrRecordBG,200);
    }

    public function BaoGiaWithSelecttionForSearchApprove(Request $request) {
        $getBaoGia = DB::table('material_cost_for_guests')
        ->where('user_id',$request->user_id)
        ->where('tinh',$request->tinh)
        ->get();
        $arrRecordBG = [];
        $gia = '';
        $strKvTd = $request->thoidiem.','.$request->khuvuc;
        foreach ($getBaoGia as $item) {
            $giaVatTu = $item->giaVatTu;
            $pos = strpos($giaVatTu, $strKvTd);
    
            if ($pos !== false) { //tim thay gia im port trong gia da co
                $arrgiaVatTu = explode(';', $giaVatTu);
                for ($key = 0; $key < count($arrgiaVatTu); $key++) {
                    if (strpos($arrgiaVatTu[$key], $strKvTd) !== false) {
                        $gia = str_replace($strKvTd.':','',$arrgiaVatTu[$key]);
                        break;
                    }
                }                         
            
            }
            $getNameTinh = DB::table('province_cities')->where('symbol_province',$item->tinh)->first();
            array_push($arrRecordBG,[
                'id'=>$item->id,
                'maVatTu'=>$item->maVatTu,
                'tenVatTu'=>$item->tenVatTu,
                'donVi'=>$item->donVi,
                'nguon'=>$item->nguon,
                'ghiChu'=>$item->ghiChu,
                'tinh'=>$getNameTinh->name_province,
                'tacGia'=>$item->tacGia,
                'giaVatTu'=>$gia,
                'khuVuc'=>$request->khuvuc,
                'thoiDiem'=>$request->thoidiem,
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
            $giaUpDate = $request->thoidiem.','.$request->khuvuc;
            $arrGiaDaCo = explode(';', $giaDaCo);
            for ($key = 0; $key < count($arrGiaDaCo); $key++) {
                if (strpos($arrGiaDaCo[$key], $giaUpDate) !== false) {
                    unset($arrGiaDaCo[$key]); //xoa bo phan tu trong mang
                    break;
                }
            }
            array_push($arrGiaDaCo, $giaUpDate.':'.$request->giaVatTu);
            $giaDaCoUpdate = implode(';', $arrGiaDaCo);
            $updated =  DB::table('material_cost_for_guests')
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

    // public function approve(Request $request, $idUserImport, $agreeOverride)
    // {
    //     $material_cost = new material_cost();
    //     $user = User::find($idUserImport);
    //     if ($user->can('create-gia-vat-tu')) {
    //         $user = User::find($idUserImport);
    //         $arrTemp = [];
    //         $arrUpdate = [];
    //         $arrData = $get = DB::table('material_cost_for_guests')
    //         ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
    //         ->get();
    //         //$arrData = json_decode($request->,true);// dungf cachs nay thi $arrData se la mang con neu khong co true thi se la object
    //         $exitsPrice = false;
    //         $arrDupplicate = [];// mảng chứa các công việc có 6 tiêu chí của $get trùng nhau nhưng chỉ lấy 1 lần thôi
    //         $arrCheck = [];// mảng xác nhận rằng đã update 1 lần của $get. mảng này sẽ có số phần tử bằng với $arrDupplicate sau khi đã check
    //         DB::beginTransaction(); // đảm bảo tính toàn vẹn dữ liệu
    //         try {
    //             foreach ($arrData as $item) {
    //                 $count = count(get_object_vars($item)); // dung ham nay de dem so luong cua 1 stdclass object sau khi decode
    //                 //giaVatTu::create([
    //                 if ($count >= 8) {// điều kiện bắt trường hợp bảng giá thiếu 1 trong các cột giá , mã hay nguồn thì sẽ lỗi. đây là trường hợp dòng chỉ có 2 - 3 ô
                       
    //                     $get = DB::table('material_cost_for_guests')
    //                         ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
    //                         ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
    //                         ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
    //                     //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
    //                         ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
    //                         ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
    //                         ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
    //                         ->where('user_id', $user ? $user->id : null)
    //                         ->get();
    //                     // chú ý phuong thức get trả về 1 colection chứ không phải là 1 mảng nên kiểu dữ liệu của $get sẽ không phải mảng
    //                     //    if($get->isEmpty()) {
    //                     //       echo('empty($get)');
    //                     //       echo(gettype($get));
    //                     //       echo($get->isEmpty());

    //                     //    }
    //                     if(count($get) > 1) {//sét trường hợp mà $get có nhiều loại vật tư trùng nhau thì chỉ cho update giá 1 làn khi duyết qua $get 1 vòng 
    //                         // còn các vòng sau thì khong update giá nữa không thì không update giá đc. ví dụ có 3 công việc 1,2,3 giống hệt nhau trong bảng vật tư
    //                         // khi đó khi lấy công việc 1 thì $get có 3 phần tử. khi lặp đến công việc 2 thì $get cũng có 3 nên ta chỉ cho update giá khi lặp $get của
    //                         // công việc 1 thôi
    //                         $exit = false;
    //                         foreach ($get as $getItem) {
    //                             if(count($arrDupplicate)>0) {
    //                                 foreach ($arrDupplicate as $arrDupplicateItem) {

    //                                     if(($arrDupplicateItem->maVatTu && $arrDupplicateItem->maVatTu !== "null" ? $arrDupplicateItem->maVatTu : null) == ($getItem->maVatTu && $getItem->maVatTu !== "null" ? $getItem->maVatTu : null)
    //                                     && ($arrDupplicateItem->tenVatTu && $arrDupplicateItem->tenVatTu !== "null" ? $arrDupplicateItem->tenVatTu : null) == ($getItem->tenVatTu && $getItem->tenVatTu !== "null" ? $getItem->tenVatTu : null)
    //                                     && ($arrDupplicateItem->donVi && $arrDupplicateItem->donVi !== "null" ? $arrDupplicateItem->donVi : null) == ($getItem->donVi && $getItem->donVi !== "null" ? $getItem->donVi : null)
    //                                     && ($arrDupplicateItem->nguon && $arrDupplicateItem->nguon !== "null" ? $arrDupplicateItem->nguon : null) == ($getItem->nguon && $getItem->nguon !== "null" ? $getItem->nguon : null)
    //                                     && ($arrDupplicateItem->ghiChu && $arrDupplicateItem->ghiChu !== "null" ? $arrDupplicateItem->ghiChu : null) == ($getItem->ghiChu && $getItem->ghiChu !== "null" ? $getItem->ghiChu : null)
    //                                     && ($arrDupplicateItem->tinh && $arrDupplicateItem->tinh !== "null" ? $arrDupplicateItem->tinh : null) == ($getItem->tinh && $getItem->tinh !== "null" ? $getItem->tinh : null))
    //                                     {
    //                                         $exit = true;
    //                                         break;
    //                                     }
    //                                 }
    //                                 if($exit == false) {
                                        
    //                                         array_push($arrDupplicate,$getItem);
    //                                         $exit = true;
    //                                         break;
                                        
    //                                 }
    //                             } else {
    //                                 array_push($arrDupplicate,$getItem);
    //                                 break;
    //                             }
    //                             if($exit == true){
    //                                 break;
    //                             }
    //                         }
    //                     }
    //                     if ($get->isEmpty()) { // không tìm thấy bản ghi nào trùng

    //                         array_push($arrTemp, [
    //                             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                             'giaVatTu' => $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null,
    //                             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                             'tacGia' => $user ? $user->name : null,
    //                             'user_id' => $user ? $user->id : null,
    //                             'vote_mark' => $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null,
    //                             'created_at' => $material_cost->freshTimestamp(),
    //                             'updated_at' => $material_cost->freshTimestamp(),
    //                         ]);
    //                     } else { // truong họp khong trung
    //                         if(count($get) == 1) {
    //                             foreach ($get as $getItem) {
    //                                 $giaDaCo = $getItem->giaVatTu;
    //                                 $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
    //                                 $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
    //                                 $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
    
    //                                 if ($pos1 !== false) { //tim thay gia im port trong gia da co
                                        
    //                                     $exitsPrice = true;
    //                                     //echo($c);
    //                                     break;
    //                                 } else { // bổ xung mới giá
    //                                     $voteDaCo = $getItem->vote_mark;
    //                                     $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
    //                                     $giaAfterUpdate = $giaDaCo . ";" . $giaImport;
    //                                     $voteAfterUpdate = $voteDaCo . ";" . $voteImport;
    //                                     DB::table('material_cost_for_guests')
    //                                         ->where('id', $getItem->id)
    //                                         ->update([
    //                                             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                                             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                                             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                                             'giaVatTu' => $giaAfterUpdate,
    //                                             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                                             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                                             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                                             'tacGia' => $user ? $user->name : null,
    //                                             'user_id' => $user ? $user->id : null,
    //                                             'vote_mark' => $voteAfterUpdate,
    //                                             'updated_at' => $material_cost->freshTimestamp(),
    //                                         ]);
    //                                     // array_push($arrUpdate, [
    //                                     //             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                                     //             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                                     //             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                                     //             'giaVatTu' => $giaAfterUpdate,
    //                                     //             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                                     //             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                                     //             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                                     //             'tacGia' => $user ? $user->name : null,
    //                                     //             'id' => $getItem->id
    //                                     // ]);
    //                                 }
    //                             }
    //                         }

    //                         if(count($get) > 1 && (count($arrDupplicate) !== count($arrCheck))) {// trường hợp khi chưa update thì $arrDupplicate và arrcheck sẽ có số phần tử k bằng nhau
    //                             foreach ($get as $getItem) {
    //                                 $giaDaCo = $getItem->giaVatTu;
    //                                 $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
    //                                 $pos = strpos($giaImport, ':'); // tách giá đến vị trí :
    //                                 $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
    
    //                                 if ($pos1 !== false) { //tim thay gia import trong gia da co
                                        
    //                                     $exitsPrice = true;
    //                                     //echo($c);
    //                                     break;
    //                                 } else { // bổ xung mới giá
    //                                     $voteDaCo = $getItem->vote_mark;
    //                                     $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
    //                                     $giaAfterUpdate = $giaDaCo . ";" . $giaImport;
    //                                     $voteAfterUpdate = $voteDaCo . ";" . $voteImport;
    //                                     DB::table('material_cost_for_guests')
    //                                         ->where('id', $getItem->id)
    //                                         ->update([
    //                                             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                                             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                                             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                                             'giaVatTu' => $giaAfterUpdate,
    //                                             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                                             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                                             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                                             'tacGia' => $user ? $user->name : null,
    //                                             'user_id' => $user ? $user->id : null,
    //                                             'vote_mark' => $voteAfterUpdate,
    //                                             'updated_at' => $material_cost->freshTimestamp(),
    //                                         ]);
    //                                     // array_push($arrUpdate, [
    //                                     //             'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                                     //             'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                                     //             'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                                     //             'giaVatTu' => $giaAfterUpdate,
    //                                     //             'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                                     //             'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                                     //             'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                                     //             'tacGia' => $user ? $user->name : null,
    //                                     //             'id' => $getItem->id
    //                                     // ]);
    //                                 }
    //                             }
    //                             array_push($arrCheck,'okcheck');
    //                         }
    //                     }
    //                     if ($exitsPrice === true) {
    //                         break;
    //                     }
    //                 }
    //             }
    //             if ($exitsPrice === true) {
    //                 if ($agreeOverride === "0") {
    //                     return response()->json([
    //                         'code' => 200,
    //                         'exist' => true,
    //                         'message' => 'Bản ghi đã tồn tại',
    //                     ]);
    //                 }
    //                 if ($agreeOverride === "1") { // dong y ghi de
    //                     foreach ($arrData as $item) {
    //                         //giaVatTu::create([
    //                         $get = DB::table('material_cost_for_guests')
    //                             ->where('maVatTu', $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null)
    //                             ->where('tenVatTu', $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null)
    //                             ->where('donVi', $item->donvi && $item->donvi !== "null" ? $item->donvi : null)
    //                         //->where('giaVatTu', $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null)
    //                             ->where('nguon', $item->nguon && $item->nguon !== "null" ? $item->nguon : null)
    //                             ->where('ghiChu', $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null)
    //                             ->where('tinh', $item->tinh && $item->tinh !== "null" ? $item->tinh : null)
    //                             ->where('user_id', $user ? $user->id : null)
    //                             ->get();

    //                         foreach ($get as $getItem) {
    //                             $giaDaCo = $getItem->giaVatTu;
    //                             $giaImport = $item->giavattu && $item->giavattu !== "null" ? $item->giavattu : null;
    //                             $voteDaCo = $getItem->vote_mark;
    //                             $voteImport = $item->vote_mark && $item->vote_mark !== "null" ? $item->vote_mark : null;
    //                             $posGia = strpos($giaImport, ':'); // tách giá đến vị trí :
    //                             $posVote = strpos($voteImport, ':'); // tách giá đến vị trí :
    //                             $pos1 = strpos($giaDaCo, substr($giaImport, 0, $pos)); // chưa vị trí tìm đc trong gia đã có
    //                             if ($pos1 !== false) { // đã tồn tại giá (nguoi dùng chọn nhầm giá va fkhu vực đã có)
    //                                 $arrgiaDaCo = explode(';', $giaDaCo);
    //                                 for ($key = 0; $key < count($arrgiaDaCo); $key++) {
    //                                     if (strpos($arrgiaDaCo[$key], substr($giaImport, 0, $posGia)) !== false) {
    //                                         unset($arrgiaDaCo[$key]); //xoa bo phan tu trong mang
    //                                         break;
    //                                     }
    //                                 }
    //                                 array_push($arrgiaDaCo, $giaImport);
    //                                 $giaDaCoUpdate = implode(';', $arrgiaDaCo);

    //                                 $arrvoteDaCo = explode(';', $voteDaCo);
    //                                 for ($key = 0; $key < count($arrvoteDaCo); $key++) {
    //                                     if (strpos($arrvoteDaCo[$key], substr($voteImport, 0, $posVote)) !== false) {
    //                                         unset($arrvoteDaCo[$key]); //xoa bo phan tu trong mang
    //                                         break;
    //                                     }
    //                                 }
    //                                 array_push($arrvoteDaCo, $voteImport);
    //                                 $voteDaCoUpdate = implode(';', $arrvoteDaCo);
    //                                 DB::table('material_cost_for_guests')
    //                                     ->where('id', $getItem->id)
    //                                     ->update([
    //                                         'maVatTu' => $item->mavattu && $item->mavattu !== "null" ? $item->mavattu : null,
    //                                         'tenVatTu' => $item->tenvattu && $item->tenvattu !== "null" ? $item->tenvattu : null,
    //                                         'donVi' => $item->donvi && $item->donvi !== "null" ? $item->donvi : null,
    //                                         'giaVatTu' => $giaDaCoUpdate,
    //                                         'nguon' => $item->nguon && $item->nguon !== "null" ? $item->nguon : null,
    //                                         'ghiChu' => $item->ghichu && $item->ghichu !== "null" ? $item->ghichu : null,
    //                                         'tinh' => $item->tinh && $item->tinh !== "null" ? $item->tinh : null,
    //                                         'tacGia' => $user ? $user->name : null,
    //                                         'user_id' => $user ? $user->id : null,
    //                                         'vote_mark' => $voteDaCoUpdate,
    //                                         'updated_at' => $material_cost->freshTimestamp(),
    //                                     ]);

    //                             }
    //                         }
    //                     }
    //                     DB::commit();
    //                     return response()->json([
    //                         'code' => 200,
    //                         'message' => 'Lưu xong giá vật tư',
    //                     ]);
    //                 }
    //                 if ($agreeOverride === "2") { // khong dong y ghi de
    //                     return;
    //                 }
    //             } else {
    //                 //  $controlUpdate = new giaVatTuController();
    //                 //  $controlUpdate->sortTasks($arrUpdate);
    //                 // //giaVatTu::updated($arrUpdate);
    //                 material_cost_for_guest::insert($arrTemp); // phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
    //                 // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
    //                 $arrTemp = [];
    //                 $arrUpdate = [];
    //                 DB::commit();
    //                 return response()->json([
    //                     'code' => 200,
    //                     'message' => 'Lưu xong giá vật tư',
    //                 ]);
    //             }
    //         } catch (Exception $exception) {
    //             DB::rollBack();
    //             $this->reportException($exception);

    //             $response = $this->renderException($request, $exception);

    //         }
    //     } else {
    //         return response([
    //             'success' => false,
    //             'message' => 'Bạn không có quyền thực hiện tác vụ này',
    //         ], 200);
    //     }
    // }
}
