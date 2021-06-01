<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\linkQlda;
class linkQldaController extends Controller
{
         public function show($id)
        {


            $host= 'http://vue.gxd.vn';
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
           foreach ($json as $value) {
            $pos1 = strpos($value, $substr1);
            $pos2 = strpos($value, $substr2);
            $pos3 = strpos($value, $substr3);
            if(!$pos1 === false||!$pos2 === false||!$pos3 === false)
            {
                $rs = $host.$value;
                return response()->json(['link' => $rs], 200);
                break;
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
            if($beforInsert !== $afterInsert&&$beforInsert>=0)
            {
                $links = linkQlda::first()->delete();
            }
            //return linkQlda::create($request->all());
        }
}
