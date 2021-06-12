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


        
        try {
            
            $giaVT = giaVatTu::create([
                'maVatTu' => $request->maVatTu,
                'tenVatTu' => $request->tenVatTu,
                'donVi' => $request->donVi,
                'nguon' => $request->nguon,
                'ghiChu' => $request->ghiChu,
                'khuVuc' => $request->khuVuc,
                
            ]);
        } catch (Exception $exception) {
             // Call report() method of App\Exceptions\Handler
            $this->reportException($exception);
            
            // Call render() method of App\Exceptions\Handler
            $response = $this->renderException($request, $exception);
        
        }
    }
}
