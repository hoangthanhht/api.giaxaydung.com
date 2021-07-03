<?php
namespace App\Traits;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
trait  StorageImageTrait{
    public function storageTraitUpload($file)
    {

            $fileNameOrigin= $file->getClientOriginalName();
            $fileNameHash= Str::random(20) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/'. 'avatar', $fileNameHash);
            $dataUploadTrait = [
                'file_name'=> $fileNameOrigin,
                'file_path'=> Storage::url($filePath)
            ];
            return $dataUploadTrait;
    }

    public function storageTraitUploadMultiple($file,$folderName)
    {
     
            $fileNameOrigin= $file->getClientOriginalName();
            $fileNameHash= Str::random(20) . '.' . $file->getClientOriginalExtension();
            $fliePath = $file->storeAs('public/'. $folderName . '/' . auth()->id(), $fileNameHash);
            $dataUploadTrait = [
                'file_name'=>$fileNameOrigin,
                'file_path'=>Storage::url($fliePath)
            ];
            return $dataUploadTrait;
   
      return null;
    }
}