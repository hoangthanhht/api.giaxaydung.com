<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApproveNoteNormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approve_note_norms', function (Blueprint $table) {
            $table->id();
            $table->text('maDinhMuc'); // cột description có kiểu là text và có thể để NULL
            $table->text('tenMaDinhMuc'); // cột price có kiểu là integer
            $table->text('donVi_VI')->nullable(); // cột price có kiểu là integer
            $table->text('tenCv_EN')->nullable(); // cột description có kiểu là text và có thể để NULL
            $table->text('donVi_EN')->nullable(); // cột price có kiểu là integer
            $table->text('url')->nullable(); // cột price có kiểu là integer
            $table->longText('ghiChuDinhMuc')->nullable(); // cột price có kiểu là integer
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approve_note_norms');
    }
}
