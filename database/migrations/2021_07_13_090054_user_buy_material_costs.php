<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserBuyMaterialCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_buy_material_costs', function (Blueprint $table) {
            $table->id();
            $table->longText('id_user_buy')->nullable();
            $table->longText('id_user_post')->nullable();
            $table->longText('describe_cost')->nullable();
            $table->longText('tinh')->nullable();
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
        Schema::dropIfExists('user_buy_material_costs');
    }
}
