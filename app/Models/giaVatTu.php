<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class giaVatTu extends Model
{
    use HasFactory;
    protected $fillable = ['maVatTu','tenVatTu','donVi','nguon','ghiChu','khuVuc'];
}
