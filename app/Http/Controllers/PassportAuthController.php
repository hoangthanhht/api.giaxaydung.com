<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\material_cost;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Validator; 
use App\Http\Requests\ruleRegister;
use App\RemoteException;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use App\Traits\StorageImageTrait;
use App\Traits\HelperTrait;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PassportAuthController extends Controller
{
    use StorageImageTrait,HelperTrait;
    /**
     * Registration
     */
    public function register(ruleRegister $request)
    {

        // $rs = $this->validate($request, [
        //     'name' => 'bail|required|min:4',
        //     'email' => 'required|string|email',
        //     'password' => 'required|min:8',
        // ]);
  
        // $validator = Validator::make($request->all(), [
        //     'name' => 'bail|required|min:4',
        //     'email' => 'required|string|email',
        //     'password' => 'required|min:8',
        // ]);//->validate();
        //$validated = $request->validated();// cái này đung với class request mình tự tạo ra sử dụng validated
        //$errors = $validator->errors();

        //DB::beginTransaction();
        try {
            
            if (isset($request->validator) && $request->validator->fails()) {
                return response()->json([
                    'code'=> 500, 
                    'message'   => $request->validator->errors()->first(),//$validator->errors()->first(),
                    'errors'    => $request->validator->errors() //hoặc $validator->errors()->toArray(),
                ]);
            }
           
            // if ($validator->fails()) {
            //     return response()->json($validator->errors(), 422);
            // }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
               
            ]);
            event(new Registered($user));// đăng ký sự kiện gưi email xác minh
            $token = $user->createToken('LaravelAuthApp')->accessToken;

            $arrSlug=[];
            //$user->roles()->get() : cái này sẽ lấy ra tất cả các bản ghi trong bảng role mà user có id  bằng id trong bảng role_id
            foreach ($user->roles()->get() as $item) {
                array_push($arrSlug, $item->slug); 
            }

            return response()->json(['token' => $token,
                                     'user' => $user,
                                     'slug' => $arrSlug                 
                                        ], 200);
            //DB::commit();
                                        // Validate the value...
        } catch (Exception $exception) {
            //DB::rollBack();
             // Call report() method of App\Exceptions\Handler
            $this->reportException($exception);
            
            // Call render() method of App\Exceptions\Handler
            $response = $this->renderException($request, $exception);
        
        }
    }
 
    /**
     * Login
     */
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
        
        
        $user = User::where('email', $request->email)->first();
        $isVerify = $user->email_verified_at;
        if($isVerify!==''&& $isVerify!== null) {

            if (auth()->attempt($data)) {
                //$user = Auth::user(); 
                
                $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
                //$user = DB::table('users')->where('email', $request->email)->get();
                $user = Auth::user();
                $arrSlug=[];
                //$user->roles()->get() : cái này sẽ lấy ra tất cả các bản ghi trong bảng role mà user có id  bằng id trong bảng role_id
                foreach ($user->roles()->get() as $item) {
                    array_push($arrSlug, $item->slug); 
                }
                return response()->json(['token' => $token,
                                          'user' => $user,
                                          'slug' => $arrSlug                 
                                                ], 200);
            } else 
            {
                return response()->json(['error' => 'Mật khẩu hoặc password không đúng',
                                         'status'=> '401'
            ], 401);
    
            }
        } else {
            return response()->json(['error' => 'Bạn chưa xác minh thông tin email',
                                         'status'=> '401'
                                        ], 401);
        }
    }   


    public function details() 
    {
        $user = Auth::user(); 
        $arrSlug=[];
        //$user->roles()->get() : cái này sẽ lấy ra tất cả các bản ghi trong bảng role mà user có id  bằng id trong bảng role_id
        foreach ($user->roles()->get() as $item) {
            array_push($arrSlug, $item->slug); 
        }
        $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
        return response()->json(['token' => $token,
                                 'user' => $user,
                                 'slug' => $arrSlug 
                                ], 200); 
    } 


    public function upload(Request $request) {
        $validator = Validator::make($request->all(),[ 
            'objFile' => 'required|mimes:jpg,png,jpeg|max:4096',
      ]);   

      if($validator->fails()) {          
           
          return response()->json(['error'=>$validator->errors()], 401);                        
       }  

 
      if ($file = $request->file('objFile')) {

            $dataUploadTrait = $this->storageTraitUpload($file);
            // $fileNameOrigin= $file->getClientOriginalName();
            // $fileNameHash= Str::random(20) . '.' . $file->getClientOriginalExtension();
            // $filePath = $file->storeAs('public/'. 'avatar', $fileNameHash);
            // $dataUploadTrait = [
            //     'file_name'=>$fileNameOrigin,
            //     'file_path'=>($filePath)
            // ];

            //$path = $file->store('public/files');
            //$name = $file->getClientOriginalName();
            $user = Auth::user();
            $avaUser = ($user->path_avatar);
            $avaUser = substr($avaUser, 1, strlen($avaUser) - 1);
            if(File::exists($avaUser)){
                File::delete($avaUser);
                //unlink('storage/avatar/CJviXUlILmYuTOv8rQYs.jpg'); // có thể dùng cái này để xóa file
                /*
                    Delete Multiple File like this way
                    Storage::delete(['upload/test.png', 'upload/test2.png']);
                */
            }else{
               
            }
            $uploadAvartar = $user->update(['path_avatar'=>($dataUploadTrait['file_path'])]);
            if($request->name) {

                $user->update(['name'=>$request->name]);
            }
            //store your file into directory and db
        //   $save = new file();
        //   $save->name = $file;
        //   $save->store_path= $path;
        //   $save->save();
            $user = Auth::user(); 
            $arrSlug=[];
            //$user->roles()->get() : cái này sẽ lấy ra tất cả các bản ghi trong bảng role mà user có id  bằng id trong bảng role_id
            foreach ($user->roles()->get() as $item) {
                array_push($arrSlug, $item->slug); 
            }
            // update lại tên bên bảng giá vật tư khi người dùng thay đổi tên
            DB::table('material_costs')
            ->where('user_id', $user->id)
            ->update([
                'tacGia' => $user ? $user->name : null,   
            ]);
             if($uploadAvartar) {
                return response()->json([ "success" => $uploadAvartar,
                "message" => "Upload file thành công",
                'user' => $user,
                'slug' => $arrSlug,
               ], 200); 

             } else {
                return response()->json([ "success" => $uploadAvartar,
                "message" => "Upload file không thành công",
                'user' => $user,
                'slug' => $arrSlug,
               ], 200); 
             }
 
      }
    }
    public function getPathFile($id) {
        $user = User::where('id',$id)->get();
        $urlAvartar = url($user[0]->path_avatar);
        return $urlAvartar;
    }


    function paginateCollection($collection, $perPage, $pageName = 'page', $fragment = null)
    {
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);
        $currentPageItems = $collection->slice(($currentPage - 1) * $perPage, $perPage);
        parse_str(request()->getQueryString(), $query);
        unset($query[$pageName]);
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $collection->count(),
            $perPage,
            $currentPage,
            [
                'pageName' => $pageName,
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'query' => $query,
                'fragment' => $fragment
            ]
        );
        return response()->json($paginator);
        //return $paginator;
    }

    public function test() {
    //      $stringArr = '';
    //      $getProvince = DB::table('material_costs')->select('tinh')->distinct()->get();
    //     foreach ($getProvince as $item) {
    //     $getPrice = DB::table('material_costs')->where('tinh', $item->tinh)->select('giaVatTu')->distinct()->get();
    //     foreach ($getPrice as $itemPrice) {
    //         $pos = strpos($itemPrice->giaVatTu, ':'); // tách giá đến vị trí :
    //         $str1 = substr($itemPrice->giaVatTu, 0, $pos); 
    //         $str1 = str_replace(',','_', $str1);
    //         $getNameProvince = DB::table('province_cities')->where('symbol_province', $item->tinh)->first();
    //         if($stringArr === '') {

    //             $stringArr = $getNameProvince->name_province . '_' .$getNameProvince->symbol_province . '_' . $str1 .';';
    //         }
    //         $stringArr = $stringArr . $getNameProvince->name_province . '_' .$getNameProvince->symbol_province . '_' . $str1 .';';
    //     }
    // }
    //     $stringArr = substr($stringArr, 0, strlen($stringArr) - 1);
    //     $arrPriceProvince = explode(";", $stringArr);



    // $pos = strpos(strtolower($this->convert_vi_to_en('Gạch bê tông đặc: KM 100A 210x100x60mm')), strtolower('gach'));
    //     if($pos!==false){
    //         echo('va day');
    //     }
  dd('123');
     
    // Gom nhóm với mỗi nhóm là 2 phần tử
    }
}