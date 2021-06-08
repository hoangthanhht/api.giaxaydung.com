<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\linkQlda;
use App\Models\noteDinhmuc;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class linkQldaController extends Controller
{
    public function show($id)
    {
        try {
            $id = strtolower($id);
            $id = str_replace('.', '-', $id);
            $host = 'https://qlda.gxd.vn';

            $length = strlen($id);
            $substr1 = substr($id, 0, $length - 1) . '0';

            $substr2 = substr($id, 0, $length - 2) . '00';
            $substr3 = substr($id, 0, $length - 3) . '000';
            $links = linkQlda::first();
            //foreach ($links as $link) {
            //dd($links->contentJsonLink);
            $link = $links->contentJsonLink;
            //dd(is_string($links->contentJsonLink));
            //}
            $json = json_decode($link, true);
            $rs = '';
            $bool_kt = false;
            if($json) {

                foreach ($json as $value) {
                    $pos = strpos($value, $id);
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
       $rsNote = '';
        $recordMaDM = DB::table('note_dinhmucs')->where('maDinhMuc', $mhcv)->get();
      
        // chu y $recordMaDM la 1 colecttion nen phai lap qua de lay tung ban ghi roi moi lay ghiChuDinhMuc
        if(count($recordMaDM)>0) {
            echo('vao');
            foreach ($recordMaDM as $item) {
                $rsNote = $item->ghiChuDinhMuc;

            }   
            if($rsNote){

                return response()->json(['noteDM' => $rsNote], 200);
            }
        }else{
            return response()->json(['error' => "Mã không phù hợp"], 400);
        }

   }


    public function getMaDM ($stringLink)
    {  
        $pos1 = 0;
        $pos = strpos($stringLink,'#');
        $stringLink = substr($stringLink, $pos, strlen($stringLink) - $pos);
        $pos = strpos($stringLink,'#');
        $substr = substr($stringLink, $pos + 4, 1);// lấy ra số đầu để kiểm tra
       if(is_numeric($substr))
        {

            for($i = $pos; ;$i++) 
            { 
               
                $substr = substr($stringLink, $i + 4, 1);
                //echo($substr)."<br/>";
                if (!is_numeric($substr))
                {
                    
                    $pos1 = $i;
                    break;
                }
            }
            $maDinhMuc = substr($stringLink, $pos + 1 , $pos1 + $pos + 3);
            $tenMaDinhMuc = substr($stringLink, strlen($maDinhMuc) + 2, strlen($stringLink) - strlen($maDinhMuc));
            $maDinhMuc = strtoupper($maDinhMuc);
            $maDinhMuc = str_replace('-', '.', $maDinhMuc);
            $tenMaDinhMuc = str_replace('-', ' ', $tenMaDinhMuc);
            return[$maDinhMuc,$tenMaDinhMuc];
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

            if(($beforInsert !== $afterInsert && $beforInsert == 0) || 
            ($beforInsert == $afterInsert && $beforInsert == 1))
            {
               
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

    public function storeTableDM ()
    {
        $obj = new linkQldaController();
        $links = linkQlda::first();

        $link = $links->contentJsonLink;

        $json = json_decode($link, true);
        
        $dmSavedArr = [];
        $dmSaved = DB::table('note_dinhmucs')
                    ->whereNotNull('ghiChuDinhMuc')
                    ->get();
        DB::table('note_dinhmucs')
                    ->whereNull('ghiChuDinhMuc')
                    ->delete();
        foreach ($dmSaved as $item) {
            array_push($dmSavedArr, $item->maDinhMuc);

        }    
        foreach ($json as $value) {
            $result = $obj->getMaDM($value);
            $checkCreate = true;
            if($result)
            {

            foreach ($dmSavedArr as $item) {
               
                if($result[0] === $item) {

                    $checkCreate = false;
                    break;
                }
            }
            if($checkCreate == true)
            {
                noteDinhmuc::create([
                    'maDinhMuc' => $result[0],
                    'tenMaDinhMuc' => $result[1],                     
                ]);

            }
        }

        }
    }


    public function getDataTableDM ()
    {
        $dinhMuc = noteDinhmuc::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;
 
        return response()->json([
            'success' => true,
            'data' => $dinhMuc
        ]);
    }


    public function updateDataDm(Request $request, $id)
    {
        $itemupdate = noteDinhmuc::find($id);
        if (!$itemupdate) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found'
            ], 400);
        }
 
        $updated = $itemupdate->fill($request->all())->save();
        if ($updated)
            return response()->json([
                'success' => true,
                'data'=>$request->all()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Post can not be updated'
            ], 500);
    }

   
}
