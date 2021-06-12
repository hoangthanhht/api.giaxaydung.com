<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\linkQlda;
use App\Models\giaVatTu;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class giaVatTuController extends Controller
{
    public function store(Request $request)
    {
      $arrData= json_decode($request->jsonData);
      DB::beginTransaction();// đảm bảo tính toàn vẹn dữ liệu
        try {
            foreach ($arrData as $item) {             
                 giaVatTu::create([
                    'maVatTu' => $item->mavattu ? $item->mavattu : null,
                    'tenVatTu' => $item->tenvattu ? $item->tenvattu :null,
                    'donVi' => $item->donvi ? $item->donvi : null,
                    'nguon' => $item->nguon ? $item->nguon : null,
                    'ghiChu' => $item->ghichu ? $item->ghichu : null,
                    'khuVuc' => $item->khuvuc ? $item->khuvuc : null,
                    
                ]);
            } 
            DB::commit();
            return response()->json([
                'code'=> 200,
                'message' => 'Lưu xong giá vật tư',
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            return response()->json([
                'code'=> 500,
                'message' => 'Không lưu được giá vật tư',
            ]);
             // Call report() method of App\Exceptions\Handler
            $this->reportException($exception);
            
            // Call render() method of App\Exceptions\Handler
            $response = $this->renderException($request, $exception);
        
        }
    }


    public function getDataTableGiaVT ()
    {
        $giaVt = giaVatTu::all(); // hàm all sẽ lất ra tất cả sản phẩm
        // $posts = auth()->user()->posts;
 
        return response()->json([
            'success' => true,
            'data' => $giaVt
        ]);
    }
}
