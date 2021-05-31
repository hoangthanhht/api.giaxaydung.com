<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->longText('contentJson'); // cột name có kiểu là varchar và giới hạn là 150 ký tự
            $table->text('dateBaocao'); // cột description có kiểu là text và có thể để NULL
            $table->longText('imgBase64')->nullable(); // cột price có kiểu là integer
            $table->text('loaiBaocao'); // cột price có kiểu là integer
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report_days');
    }
}
