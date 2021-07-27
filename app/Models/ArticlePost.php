<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticlePost extends Model
{
    use HasFactory;
    protected $fillable = ['chuDe','tieuDe','editorData','tacGia','user_id'];
}
