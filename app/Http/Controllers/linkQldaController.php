<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\linkQlda;
use App\Models\note_norms;
use App\Models\approve_note_norm;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class linkQldaController extends Controller
{
    public function show($mhcv)
    {
        try {
            $mhcv = strtolower($mhcv);
            $mhcv = str_replace('.', '-', $mhcv);
            $host = 'https://qlda.gxd.vn';

            $length = strlen($mhcv);
            $substr1 = substr($mhcv, 0, $length - 1) . '0';

            $substr2 = substr($mhcv, 0, $length - 2) . '00';
            $substr3 = substr($mhcv, 0, $length - 3) . '000';
            $links = linkQlda::first();
            //foreach ($links as $link) {
            //dd($links->contentJsonLink);
            $link = $links->contentJsonLink;
            //dd(is_string($links->contentJsonLink));
            //}
            $json = json_decode($link, true);
            $rs = '';
            $bool_kt = false;
            if ($json) {

                foreach ($json as $value) {
                    $pos = strpos($value, $mhcv);
                    if (!$pos === false) {

                        $rs = $host . $value;
                        $bool_kt = true;
                        return $rs; //response()->json(['link' => $rs], 200);
                        break;
                    }
                }

                if ($bool_kt === false) {
                    foreach ($json as $value) {
                        $pos1 = strpos($value, $substr1);
                        if (!$pos1 === false) {
                            $rs = $host . $value;
                            $bool_kt = true;
                            return $rs; //response()->json(['link' => $rs], 200);
                            break;
                        }
                    }
                }

                if ($bool_kt === false) {
                    foreach ($json as $value) {
                        $pos2 = strpos($value, $substr2);
                        if (!$pos2 === false) {
                            $rs = $host . $value;

                            $bool_kt = true;
                            return $rs; //response()->json(['link' => $rs], 200);
                            break;
                        }
                    }
                }
                if ($bool_kt === false) {
                    foreach ($json as $value) {
                        $pos3 = strpos($value, $substr3);
                        if (!$pos3 === false) {
                            $rs = $host . $value;
                            $bool_kt = true;
                            return $rs; //response()->json(['link' => $rs], 200);
                            break;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            echo "Message: " . $e->getMessage();
            echo "";
            echo "getCode(): " . $e->getCode();
            echo "";
            echo "__toString(): " . $e->__toString();
        }
    }

    public function getNoteDM($mhcv)
    {
        $strArr = [];
        $length = strlen($mhcv);
        array_push($strArr, $mhcv);
        $substr1 = substr($mhcv, 0, $length - 1) . '0';
        array_push($strArr, $substr1);
        $substr2 = substr($mhcv, 0, $length - 2) . '00';
        array_push($strArr, $substr2);
        $substr3 = substr($mhcv, 0, $length - 3) . '000';
        array_push($strArr, $substr3);
        $rsNote = '';
        foreach ($strArr as $item) {
            $recordMaDM = DB::table('note_norms')->where('maDinhMuc', $item)->get();
            if (count($recordMaDM) > 0) {
                break;
            }
        }

        // chu y $recordMaDM la 1 colecttion nen phai lap qua de lay tung ban ghi roi moi lay ghiChuDinhMuc
        if (count($recordMaDM) > 0) {
            foreach ($recordMaDM as $item) {
                $rsNote = $item->ghiChuDinhMuc;
            }
            if ($rsNote) {

                return $rsNote;
            }
        } else {
            return response()->json(['error' => "Mã không phù hợp"], 400);
        }
    }

    // hàm tách chuỗi để đưa vào bảng định mức
    public function getMaDM($stringLink)
    {
        $pos1 = 0;
        $pos = strpos($stringLink, '#');
        $stringLink = substr($stringLink, $pos, strlen($stringLink) - $pos);
        $pos = strpos($stringLink, '#');
        $substr = substr($stringLink, $pos + 4, 1); // lấy ra số đầu để kiểm tra
        if (is_numeric($substr)) {

            for ($i = $pos;; $i++) {

                $substr = substr($stringLink, $i + 4, 1);
                //echo($substr)."<br/>";
                if (!is_numeric($substr)) {

                    $pos1 = $i;
                    break;
                }
            }
            $maDinhMuc = substr($stringLink, $pos + 1, $pos1 + $pos + 3);
            $tenMaDinhMuc = substr($stringLink, strlen($maDinhMuc) + 2, strlen($stringLink) - strlen($maDinhMuc));
            $maDinhMuc = strtoupper($maDinhMuc);
            $maDinhMuc = str_replace('-', '.', $maDinhMuc);
            $tenMaDinhMuc = str_replace('-', ' ', $tenMaDinhMuc);
            return [$maDinhMuc, $tenMaDinhMuc];
        }
    }

    public function store(Request $request)
    {
        try {
            $obj = new linkQldaController();
            $beforInsert = linkQlda::all()->count();

            linkQlda::firstOrCreate(
                [
                    'contentJsonLink' => $request->contentJsonLink,
                ]
            );
            $afterInsert = linkQlda::all()->count();

            if (($beforInsert !== $afterInsert && $beforInsert == 0) ||
                ($beforInsert == $afterInsert && $beforInsert == 1)
            ) {

                $obj->storeTableDM($request);
            }
            if ($beforInsert !== $afterInsert && $beforInsert >= 1) {
                linkQlda::first()->delete();
                $obj->storeTableDM($request);
            }
        } catch (Exception $e) {
            echo "Message: " . $e->getMessage();
            echo "";
            echo "getCode(): " . $e->getCode();
            echo "";
            echo "__toString(): " . $e->__toString();
        }
        //return linkQlda::create($request->all());
    }
    // ham nay lay da ta tu trang quan ly du an ve
    public function storeTableDM(Request $request)
    {
        DB::beginTransaction();
        try {
            $note_norm = new note_norms();
            $obj = new linkQldaController();
            $links = linkQlda::first();

            $link = $links->contentJsonLink;

            $json = json_decode($link, true);
            $dmTableArr = []; //mảng chứa các bản ghi sẽ đc ghi vào db để tránh trường hợp số lượng bản ghi lớn gặp lỗi

            foreach ($json as $value) {
                $result = $obj->getMaDM($value);
                if ($result) {
                    $getMDM = DB::table('note_norms')
                        ->where('maDinhMuc', $result[0])
                        ->get();
                    if (!$getMDM->isEmpty()) {
                        $get = DB::table('note_norms')
                            ->where('maDinhMuc', $result[0])
                            ->whereNotNull('ghiChuDinhMuc')
                            ->get();
                        if ($get->isEmpty()) {
                            DB::table('note_norms')
                                ->where('maDinhMuc', $result[0])
                                ->delete();
                            array_push($dmTableArr, [
                                'maDinhMuc' => $result[0],
                                'tenMaDinhMuc' => $result[1],
                                'created_at' => $note_norm->freshTimestamp(),
                                'updated_at' => $note_norm->freshTimestamp(),
                            ]);
                        } else {
                            DB::table('note_norms')
                                ->where('id', $get->id)
                                ->update([
                                    'maDinhMuc' => $result[0],
                                    'tenMaDinhMuc' => $result[1],
                                ]);
                        }
                    } else {
                        array_push($dmTableArr, [
                            'maDinhMuc' => $result[0],
                            'tenMaDinhMuc' => $result[1],
                            'created_at' => $note_norm->freshTimestamp(),
                            'updated_at' => $note_norm->freshTimestamp()
                        ]);
                    }
                }
            }
            note_norms::insert($dmTableArr);
            $dmTableArr = [];
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            $this->reportException($exception);

            //$response = $this->renderException($request, $exception);

        }
    }

    public function getAllDataTableDm()
    {
        $dinhMuc = note_norms::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json([
            'success' => true,
            'data' => $dinhMuc,
        ]);
    }

    public function getAllDataTableDmContribute()
    {
        $dinhMuc = approve_note_norm::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json([
            'success' => true,
            'data' => $dinhMuc,
        ]);
    }

    public function getDataTableDM()
    {
        $dinhMuc = note_norms::paginate(20);
        // $posts = auth()->user()->posts;

        return response()->json($dinhMuc);
    }

    public function getDataTableDmContribute()
    {
        $dinhMuc = approve_note_norm::paginate(20);
        // $posts = auth()->user()->posts;

        return response()->json($dinhMuc);
    }

    public function updateDataDm(Request $request, $iddm, $iduser)
    {
        $v = $request->id;
        $user = User::find($iduser);
        // $pm = $u->getAllPermissions($u->permissions[0]);
        if ($user->can('edit-dinh-muc')) {
            $itemupdate = note_norms::find($iddm);
            if (!$itemupdate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found',
                ], 400);
            }
            //$updated = $itemupdate->fill($request->all())->save();
            $updated = DB::table('note_norms')
            ->where('id', $request->id)
            ->update([
                'maDinhMuc' => $request->maDinhMuc ? $request->maDinhMuc : '',
                'tenMaDinhMuc' => $request->tenMaDinhMuc ? $request->tenMaDinhMuc : '',
                'ghiChuDinhMuc' => $request->ghiChuDinhMuc ? $request->ghiChuDinhMuc : '',
                'donVi_VI' => $request->donVi_VI ? $request->donVi_VI : '',
                'tenCv_EN' => $request->tenCv_EN ? $request->tenCv_EN : '',
                'donVi_EN' => $request->donVi_EN ? $request->donVi_EN : '',
                'url' => $request->url ? $request->url : '',
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

    public function updateDataDmContribute(Request $request, $iddm, $iduser)
    {
        $user = User::find($iduser);
        // $pm = $u->getAllPermissions($u->permissions[0]);
        if ($user->can('edit-dinh-muc')) {
            $itemupdate = approve_note_norm::find($iddm);
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
    // ham nay cho nguoi dung up dinh muc len tu phan mem
    public function CreateDinhMucContribute(Request $request)
    {
        $note_norm = new note_norms();
        // $get = DB::table('approve_note_norms')
        // ->where('maDinhMuc', $request->maDinhMuc)
        // ->where('tenMaDinhMuc', $request->tenMaDinhMuc)
        // ->where('donVi_VI', $request->donVi_VI)
        // ->where('tenCv_EN', $request->tenCv_EN)
        // ->where('donVi_EN', $request->donVi_EN)
        // ->where('ghiChuDinhMuc', $request->ghiChuDinhMuc)
        // ->get();
        //if ($get->isEmpty()) {// trường hợp không có mã hoặc mã chưa tồn tại trong bảng norm thì thêm mới
        DB::table('approve_note_norms')
            ->where('id', $request->id)
            ->insert([
                'maDinhMuc' => $request->maDinhMuc && $request->maDinhMuc !== "null" ? $request->maDinhMuc : null,
                'tenMaDinhMuc' => $request->tenMaDinhMuc && $request->tenMaDinhMuc !== "null" ? $request->tenMaDinhMuc : null,
                'donVi_VI' => $request->donVi_VI && $request->donVi_VI !== "null" ? $request->donVi_VI : null,
                'tenCv_EN' => $request->tenCv_EN && $request->tenCv_EN !== "null" ? $request->tenCv_EN : null,
                'donVi_EN' => $request->donVi_EN && $request->donVi_EN !== "null" ? $request->donVi_EN : null,
                'ghiChuDinhMuc' => $request->ghiChuDinhMuc && $request->ghiChuDinhMuc !== "null" ? $request->ghiChuDinhMuc : null,
                'created_at' => $note_norm->freshTimestamp(),
                'updated_at' => $note_norm->freshTimestamp(),
            ]);
        //}
    }
    // ham nay import dinh muc tu file excel
    public function CreateDinhMuc(Request $request, $idUserImport)
    {
        $note_norm = new note_norms();
        $user = User::find($idUserImport);
        if ($user->can('create-dinh-muc')) {
            $user = User::find($idUserImport);
            $arrTemp = [];
            $arrUpdate = [];
            $arrData = json_decode($request->jsonData);
            DB::beginTransaction();
            try {
                foreach (array_chunk($arrData, 1000) as $ins) {
                    foreach ($ins as $item) {
                        $get = DB::table('note_norms')
                            ->where('maDinhMuc', $item->madongia && $item->madongia !== "null" ? $item->madongia : null)
                            ->get();
                        if ($get->isEmpty()) { // trường hợp không có mã hoặc mã chưa tồn tại trong bảng norm thì thêm mới
                            array_push($arrTemp, [
                                'maDinhMuc' => $item->madongia && $item->madongia !== "null" ? $item->madongia : null,
                                'tenMaDinhMuc' => $item->tendongiavi && $item->tendongiavi !== "null" ? $item->tendongiavi : null,
                                'donVi_VI' => $item->donvivi && $item->donvivi !== "null" ? $item->donvivi : null,
                                'tenCv_EN' => $item->tendongiaen && $item->tendongiaen !== "null" ? $item->tendongiaen : null,
                                'donVi_EN' => $item->donvien && $item->donvien !== "null" ? $item->donvien : null,
                                'url' => $item->url && $item->url !== "null" ? $item->url : null,
                                'ghiChuDinhMuc' => $item->note && $item->note !== "null" ? $item->note : null,
                                //'created_at' => $note_norm->freshTimestamp(),
                                //'updated_at' => $note_norm->freshTimestamp(),
                            ]);
                        } else {
                            foreach ($get as $getItem) {
                                $noteDaco = $getItem->ghiChuDinhMuc;
                                if ($noteDaco) {
                                    $noteUpdate = $noteDaco . ';' . $item->note && $item->note !== "null" ? $item->note : null;
                                } else {
                                    $noteUpdate = $item->note && $item->note !== "null" ? $item->note : null;
                                }
                                $madongia = ($item->madongia && $item->madongia !== "null" ? $item->madongia : null);

                                $tendongiavi = ($item->tendongiavi && $item->tendongiavi !== "null" ? $item->tendongiavi : null);

                                $donvivi = ($item->donvivi && $item->donvivi !== "null" ? $item->donvivi : null);

                                $tendongiaen = ($item->tendongiaen && $item->tendongiaen !== "null" ? $item->tendongiaen : null);

                                $donvien = ($item->donvien && $item->donvien !== "null" ? $item->donvien : null);

                                $url = ($item->url && $item->url !== "null" ? $item->url : null);

                                DB::table('note_norms')
                                    ->where('id', $getItem->id)
                                    ->update([
                                        'maDinhMuc' => !$item->madongia || $item->madongia == "null" || ($getItem->maDinhMuc && $getItem->maDinhMuc !== "null" && $madongia == $getItem->maDinhMuc) ?  $getItem->maDinhMuc :
                                            $madongia,
                                        'tenMaDinhMuc' => !$item->tendongiavi || $item->tendongiavi == "null" || ($getItem->tenMaDinhMuc && $getItem->tenMaDinhMuc !== "null" && $tendongiavi == $getItem->tenMaDinhMuc) ? $getItem->tenMaDinhMuc :
                                            $tendongiavi,
                                        'donVi_VI' => !$item->donvivi || $item->donvivi == "null" || ($getItem->donVi_VI && $getItem->donVi_VI !== "null" && $donvivi == $getItem->donVi_VI) ? $getItem->donVi_VI :
                                            $donvivi,
                                        'tenCv_EN' => !$item->tendongiaen || $item->tendongiaen == "null" || ($getItem->tenCv_EN && $getItem->tenCv_EN !== "null" && $tendongiaen == $getItem->tenCv_EN) ? $getItem->tenCv_EN :
                                            $tendongiaen,
                                        'donVi_EN' => !$item->donvien || $item->donvien == "null" || ($getItem->donVi_EN && $getItem->donVi_EN !== "null" && $donvien == $getItem->donVi_EN) ? $getItem->donVi_EN :
                                            $donvien,
                                        'url' => !$item->url || $item->url == "null" || ($getItem->url && $getItem->url !== "null" && $url == $getItem->url) ? $getItem->url :
                                            $url,
                                        'ghiChuDinhMuc' => $noteUpdate,
                                        //'created_at' => $note_norm->freshTimestamp(),
                                        //'updated_at' => $note_norm->freshTimestamp(),
                                    ]);
                                DB::commit();
                                return response()->json([
                                    'success' => true,
                                    'msg' => 'Hoàn tất',
                                ]);
                            }
                        }
                    }
                    note_norms::insert($arrTemp);
                    $arrTemp = [];
                }
                // array_chunk($arrTemp,1000);

                //DB::table('note_norms')->insert($ins); 
                // note_norms::insert($ins); 


                //note_norms::insert($arrTemp); // phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
                // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
                // dung eloquen khi dung voi insert thi khong chen dc ngay vao create_at.phai dung ham create thi moi tao dc create_at
                DB::commit();
          
                return response()->json([
                    'success' => true,
                    'data'=>$arrData,
                    'msg' => 'Hoàn tất',
                ]);
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


    public function handleApprove(Request $request)
    {
        $user = User::find($request->idUser);
        if ($user->can('approve-dinh-muc')) {
            DB::beginTransaction();
            try {
                $note_norm = new note_norms();
                $get = DB::table('note_norms')
                    ->where('maDinhMuc', $request->maDinhMuc && $request->maDinhMuc !== "null" ? $request->maDinhMuc : null)
                    ->get();
                if ($get->isEmpty()) { // trường hợp không có mã hoặc mã chưa tồn tại trong bảng norm thì thêm mới
                    DB::table('note_norms')
                        ->where('id', $request->id)
                        ->insert([
                            'maDinhMuc' => $request->maDinhMuc && $request->maDinhMuc !== "null" ? $request->maDinhMuc : null,
                            'tenMaDinhMuc' => $request->tenMaDinhMuc && $request->tenMaDinhMuc !== "null" ? $request->tenMaDinhMuc : null,
                            'donVi_VI' => $request->donVi_VI && $request->donVi_VI !== "null" ? $request->donVi_VI : null,
                            'tenCv_EN' => $request->tenCv_EN && $request->tenCv_EN !== "null" ? $request->tenCv_EN : null,
                            'donVi_EN' => $request->donVi_EN && $request->donVi_EN !== "null" ? $request->donVi_EN : null,
                            'ghiChuDinhMuc' => $request->ghiChuDinhMuc && $request->ghiChuDinhMuc !== "null" ? $request->ghiChuDinhMuc : null,
                            'created_at' => $note_norm->freshTimestamp(),
                            'updated_at' => $note_norm->freshTimestamp(),
                        ]);
                    DB::table('approve_note_norms')
                        ->where('id', $request->id)
                        ->delete();
                    DB::commit();
                    return response()->json([
                        'code' => 200,
                        'message' => 'Lưu xong định mức',
                    ]);
                } else {
                    foreach ($get as $getItem) {
                        $noteDaco = $getItem->ghiChuDinhMuc;
                        if ($noteDaco) {
                            $noteUpdate = $noteDaco . ';' . ($request->ghiChuDinhMuc && $request->ghiChuDinhMuc !== "null" ? $request->ghiChuDinhMuc : null);
                        } else {
                            $noteUpdate = $request->ghiChuDinhMuc && $request->ghiChuDinhMuc !== "null" ? $request->ghiChuDinhMuc : null;
                        }


                        DB::table('note_norms')
                            ->where('id', $getItem->id)
                            ->update([
                                'maDinhMuc' => !$request->maDinhMuc || $request->maDinhMuc == "null" ||
                                    ($getItem->maDinhMuc && $getItem->maDinhMuc !== "null" && $request->maDinhMuc == $getItem->maDinhMuc) ?  $getItem->maDinhMuc :
                                    $request->maDinhMuc,
                                'tenMaDinhMuc' => !$request->tenMaDinhMuc || $request->tenMaDinhMuc == "null" ||
                                    ($getItem->tenMaDinhMuc && $getItem->tenMaDinhMuc !== "null" && $request->tenMaDinhMuc == $getItem->tenMaDinhMuc) ? $getItem->tenMaDinhMuc :
                                    $request->tenMaDinhMuc,
                                'donVi_VI' => !$request->donVi_VI || $request->donVi_VI == "null" ||
                                    ($getItem->donVi_VI && $getItem->donVi_VI !== "null" && $request->donVi_VI == $getItem->donVi_VI) ? $getItem->donVi_VI :
                                    $request->donVi_VI,
                                'tenCv_EN' => !$request->tenCv_EN || $request->tenCv_EN == "null" ||
                                    ($getItem->tenCv_EN && $getItem->tenCv_EN !== "null" && $request->tenCv_EN == $getItem->tenCv_EN) ? $getItem->tenCv_EN :
                                    $request->tenCv_EN,
                                'donVi_EN' => !$request->donVi_EN || $request->donVi_EN == "null" ||
                                    ($getItem->donVi_EN && $getItem->donVi_EN !== "null" && $request->donVi_EN == $getItem->donVi_EN) ? $getItem->donVi_EN :
                                    $request->donVi_EN,

                                'ghiChuDinhMuc' => $noteUpdate,
                                //'created_at' => $note_norm->freshTimestamp(),
                                //'updated_at' => $note_norm->freshTimestamp(),
                            ]);
                        DB::table('approve_note_norms')
                            ->where('id', $request->id)
                            ->delete();
                        DB::commit();
                        return response()->json([
                            'code' => 200,
                            'message' => 'Lưu xong định mức',
                        ]);
                    }
                }
            } catch (Exception $exception) {
                DB::rollBack();
                $this->reportException($exception);

                //$response = $this->renderException($request, $exception);

            }
        } else {
            return response([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện tác vụ này',
            ], 200);
        }
    }

    public function handleDeleteNoteDmContribute(Request $request, $id)
    {
        $a = $request->iddm;
        $user = User::find($request->idUser);
        if ($user->can('delete-dinh-muc')) {
            DB::table('approve_note_norms')
                ->where('id', $id)
                ->delete();
            return response()->json([
                'code' => 200,
                'message' => 'Đã xóa định mức',
            ]);
        } else {
            return response([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện tác vụ này',
            ], 200);
        }
    }
}
