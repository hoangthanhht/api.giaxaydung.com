<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\linkQlda;
use App\Models\noteDinhmuc;
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
            $recordMaDM = DB::table('note_dinhmucs')->where('maDinhMuc', $item)->get();
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
                ($beforInsert == $afterInsert && $beforInsert == 1)) {

                $obj->storeTableDM();
            }
            if ($beforInsert !== $afterInsert && $beforInsert >= 1) {
                linkQlda::first()->delete();
                $obj->storeTableDM();
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

    public function storeTableDM()
    {
        $obj = new linkQldaController();
        $links = linkQlda::first();

        $link = $links->contentJsonLink;

        $json = json_decode($link, true);
        $dmTableArr = []; //mảng chứa các bản ghi sẽ đc ghi vào db để tránh trường hợp số lượng bản ghi lớn gặp lỗi
        $dmSavedArr = []; //mảng chứa các mã định mức đã có ghi chú trong bảng
        $dmSaved = DB::table('note_dinhmucs')
            ->whereNotNull('ghiChuDinhMuc')
            ->get();
        DB::table('note_dinhmucs')
            ->whereNull('ghiChuDinhMuc')
            ->delete();
        foreach ($dmSaved as $item) {
            array_push($dmSavedArr, $item->maDinhMuc); //lọc ra để lấy mã đinh mức trong mảng $dmSavedArr

        }
        foreach ($json as $value) {
            $result = $obj->getMaDM($value);
            $checkCreate = true; //biến kiêm tra mã đã có trong bảng hay chưa
            if ($result) {

                foreach ($dmSavedArr as $item) {

                    if ($result[0] === $item) {

                        $checkCreate = false;
                        break;
                    }
                }
                if ($checkCreate == true) {
                    //noteDinhmuc::create(
                    array_push($dmTableArr, [
                        'maDinhMuc' => $result[0],
                        'tenMaDinhMuc' => $result[1],
                    ]);

                }
            }

        }
        noteDinhmuc::insert($dmTableArr);
        $dmTableArr = [];
    }
    
    public function getAllDataTableDm()
    {
        $dinhMuc = noteDinhmuc::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json([
            'success' => true,
            'data' => $dinhMuc,
        ]);
    }


    public function getDataTableDM()
    {
        $dinhMuc = noteDinhmuc::paginate(20); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;

        return response()->json($dinhMuc);
    }


    public function updateDataDm(Request $request, $iddm, $iduser)
    {
        $user = User::find($iduser);
        // $pm = $u->getAllPermissions($u->permissions[0]);
        //if ($user->can('create-tasks')) {
            $itemupdate = noteDinhmuc::find($iddm);
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

}
