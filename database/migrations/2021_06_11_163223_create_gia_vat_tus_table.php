<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiaVatTusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gia_vat_tus', function (Blueprint $table) {
            $table->id();
            $table->longText('maVatTu')->nullable(); 
            $table->longText('tenVatTu')->nullable(); 
            $table->longText('donVi')->nullable(); 
            $table->longText('nguon')->nullable(); 
            $table->longText('ghiChu')->nullable(); 
            $table->longText('khuVuc')->nullable(); 
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
        Schema::dropIfExists('gia_vat_tus');
    }
}
