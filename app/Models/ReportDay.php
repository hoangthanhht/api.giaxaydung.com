<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDay extends Model
{
    use HasFactory;
    protected $fillable = ['contentJson','dateBaocao','imgBase64'];
}
