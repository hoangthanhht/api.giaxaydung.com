<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\linkQlda;
use App\Models\giaVatTu;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
header('Access-Control-Allow-Origin', '*');
header("Access-Control-Allow-Methods: GET, POST");
class giaVatTuController extends Controller
{
    public function store(Request $request)
    {
        $arrTemp = [];
      $arrData= json_decode($request->jsonData);
      //DB::beginTransaction();// đảm bảo tính toàn vẹn dữ liệu
        try {
            foreach ($arrData as $item) {             
                 //giaVatTu::create([
                    array_push($arrTemp,[
                    'maVatTu' => $item->mavattu ? $item->mavattu : null,
                    'tenVatTu' => $item->tenvattu ? $item->tenvattu :null,
                    'donVi' => $item->donvi ? $item->donvi : null,
                    'nguon' => $item->nguon ? $item->nguon : null,
                    'ghiChu' => $item->ghichu ? $item->ghichu : null,
                    'khuVuc' => $item->khuvuc ? $item->khuvuc : null,
                    
                ]);
                
            } 
            giaVatTu::insert($arrTemp);// phải dùng cách này: lặp và đẩy dữ liệu cần tọa vào 1 mảng trung gian sau đó mới ghi vào db
            // để tạo bản ghi số lượng lớn nếu không sẽ gặp lỗi cors
            //DB::commit();
            $arrTemp=[];
            return response()->json([
                'code'=> 200,
                'message' => 'Lưu xong giá vật tư',
            ]);
        } catch (Exception $exception) {
            DB::rollBack();

            // return response()->json([
            //     'code'=> 500,
            //     'message' => 'Không lưu được giá vật tư',
            // ]);
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
