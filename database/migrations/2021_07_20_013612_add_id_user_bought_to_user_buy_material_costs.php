<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdUserBoughtToUserBuyMaterialCosts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_buy_material_costs', function (Blueprint $table) {
            $table->longText('id_user_bought')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_buy_material_costs', function (Blueprint $table) {
            $table->dropColumn('id_user_bought');
        });
    }
}
