<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnEnToTableNoteNorm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('note_norms', function (Blueprint $table) {
            $table->text('donVi_VI')->nullable(); // cột description có kiểu là text và có thể để NULL
            $table->text('tenCv_EN')->nullable(); // cột description có kiểu là text và có thể để NULL
            $table->text('donVi_EN')->nullable(); // cột price có kiểu là integer
            $table->text('url')->nullable(); // cột price có kiểu là integer
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('note_norms', function (Blueprint $table) {
            $table->dropColumn('tenCv_EN');
            $table->dropColumn('donVi_EN');
            $table->dropColumn('url');
        });
    }
}
