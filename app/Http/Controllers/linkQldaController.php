<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\linkQlda;
class linkQldaController extends Controller
{
         public function show($id)
        {

            $id = str_replace('.', '-', $id);
            $host= 'https:\/\/qlda.gxd.vn';
            
            $length = strlen($id);
            $substr1 = substr( $id, 0, $length - 1 ).'0';
            
            $substr2 = substr( $id, 0, $length - 2 ).'00';
            $substr3 = substr( $id, 0, $length - 3 ).'000';
           $links = linkQlda::first();
           //foreach ($links as $link) {
            //dd($links->contentJsonLink);
            $link = $links->contentJsonLink;
            //dd(is_string($links->contentJsonLink));
           //}
           $json = json_decode($link, true);
           $rs;
           $bool_kt = false;
           
           foreach ($json as $value) {
            $pos = strpos($value, $id);
            if(!$pos === false)
            {
               
                $rs = $host.$value;
                $rs = str_replace('\\', '', $rs);
                $bool_kt = true;
                return $rs;//response()->json(['link' => $rs], 200);
                break;
            }
           
            }
        
        if($bool_kt === false)
        {
            foreach ($json as $value) {
                $pos1 = strpos($value, $substr1);
                if(!$pos1 === false)
                {
                    $rs = $host.$value;
                    $bool_kt = true;
                    return response()->json(['link' => $rs], 200);
                    break;
                }
               
                }
        }

        if($bool_kt === false)
        {
            foreach ($json as $value) {
                $pos2 = strpos($value, $substr2);
                if(!$pos1 === false)
                {
                    $rs = $host.$value;
                    $bool_kt = true;
                    return response()->json(['link' => $rs], 200);
                    break;
                }
               
                }
        }
        if($bool_kt === false)
        {
            foreach ($json as $value) {
                $pos3 = strpos($value, $substr3);
                if(!$pos1 === false)
                {
                    $rs = $host.$value;
                    $bool_kt = true;
                    return response()->json(['link' => $rs], 200);
                    break;
                }
               
                }
        }
                 
        }

        public function store(Request $request)
        {
            $beforInsert = linkQlda::all()->count();
                     
             $link = linkQlda::firstOrCreate(
                [
                    'contentJsonLink' => $request->contentJsonLink
                ]
               );
            $afterInsert = linkQlda::all()->count();
            if($beforInsert !== $afterInsert && $beforInsert >= 1)
            {
                $links = linkQlda::first()->delete();
            }
          
            //return linkQlda::create($request->all());
        }
}
