<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ArticlePost;
use App\Models\User;
class ArticlePostController extends Controller
{
    public function createArticle(Request $request) {
        $user = User::find($request->idUser);
        $a = $user->name;
        $chuDe = $request->chuDe;
        $ArticlePost = ArticlePost::create([
            //$ArticlePost = DB::table('article_posts')->insert([
            'chuDe' => $request->chuDe,
            'tieuDe' => $request->tieuDe,
            'editorData' => $request->editorData,
            'tacGia' => $user->name,
            'user_id' =>  $request->idUser,
        ]);
        if($ArticlePost) {
            return response()->json([
                'success' => true,
                'msg' => 'Tạo bài viết thành công',
            ]);
        }else{
            return response()->json([
                'success' => false,
                'msg' => 'Tạo bài viết không thành công',
            ]);
        }
    }

    public function getListArticle(){
        $post = ArticlePost::paginate(20);
        // $posts = auth()->user()->posts;

        return response()->json($post);
    }
}
