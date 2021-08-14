<?php

namespace App\Http\Controllers;

use Facade\FlareClient\Http\Response as HttpResponse;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateHttpResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
//use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Response;
class BackupController extends Controller
{
    public function index()
    {
     
            $allFiles = Storage::disk('custom')->allFiles();
            $allFiles1 = Storage::disk('custom');
            
            // Sort files by modified time DESC
            // usort($allFiles, function ($a, $b) {
            //     return -1 * strcmp($a->getMTime(), $b->getMTime());
            // });
            $files = array();

            foreach ($allFiles as $file) {
                //File::basename($file);
                //$file['size'] =  File::size(public_path() . '/storage/bk/' . $file);
                //$file['name'] =  File::name(public_path() . '/storage/bk/' . $file);
                //$file['extension'] =  File::extension(public_path() . '/storage/bk/' . $file);
                $files[] = $this->fileInfo(pathinfo(public_path() . '/storage/bk/' . $file),$allFiles1,$file);
                 //$files['name'] = date(DATE_RFC2822, $allFiles1->lastModified($file));
                 //$f = date('d-m-Y',File::lastModified(public_path() . '/storage/bk/' . $file));
                 //$lastmodified = \DateTime::createFromFormat('U', $f);
                 //$lastmodified->setTimezone(new \DateTimeZone('Asia/Ho_Chi_Minh'));
                 
            }
            $collection = collect($files);
            $rsFiles = $collection->paginate(20);
            return $rsFiles;

    }

    public function fileInfo($filePath,$allFiles1,$file1)
    {
        $file = array();
        $file['file'] = $filePath['filename'].'.'.$filePath['extension'];
        //$file['extension'] = $filePath['extension'];
        $size = filesize($filePath['dirname'] . '/' . $filePath['basename']);
        $file['size'] = $size>(1048576)? number_format($size/ 1048576,2).' Mb':$size . ' bytes';
        //number_format($file_size / 1048576,2);
        $file['date'] = date('d-m-Y', $allFiles1->lastModified($file1));
        return $file;
    }

    public function download($fileName)
    {
        // $file = Storage::disk('custom')->get($fileName);
  
        // return (new Response($file, 200))
        //       ->header('Content-Type', 'txt');
          //$filePath = public_path() . '/storage/bk/' . $fileName;
        //  $content = file_get_contents($filePath);
        //  return response($content)->withHeaders([
        //     'Content-Type'=> mime_content_type($filePath)
        //  ]);
    	 //$headers = ['Content-Type: application/sql'];
    	// $fileName1 = time().'.txt';
         //return Storage::download('http://127.0.0.1:8000/storage/bk/' . $fileName);
    	//return Response::download($filePath, '$fileName1', $headers);
    	//return response()->download($filePath, '$fileName1', $headers);
        return response()->download(public_path() . '/storage/bk/' . $fileName);
    }

    public function destroy($fileName)
    {
        $rsDelete = '';
        if (file_exists(public_path() . '/storage/bk/' . $fileName)) {
            $rsDelete = unlink(public_path() . '/storage/bk/' . $fileName);
        }
        if($rsDelete){
            return response()->json(['msg' =>'Xóa thành công',
            ], 200);
        }else{
            return response()->json(['msg' =>'Xóa không thành công',
        ], 200);
        }
    }
}
