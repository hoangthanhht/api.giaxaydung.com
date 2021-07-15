<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialCostForGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_cost_for_guests', function (Blueprint $table) {
            $table->id();
            $table->longText('maVatTu')->nullable(); 
            $table->longText('tenVatTu')->nullable(); 
            $table->longText('donVi')->nullable(); 
            $table->longText('giaVatTu')->nullable();// bao gồm tỉnh khu vực, thời gian
            $table->longText('ghiChu')->nullable(); 
            $table->longText('nguon')->nullable(); 
            $table->longText('tinh')->nullable(); 
            $table->longText('tacGia')->nullable(); 
            $table->integer('user_id')->nullable();
            $table->longText('vote_mark')->nullable();
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
        Schema::dropIfExists('material_cost_for_guests');
    }
}
