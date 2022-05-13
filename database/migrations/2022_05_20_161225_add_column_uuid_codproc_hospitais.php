<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUuidCodprocHospitais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hospitais', function (Blueprint $table) {

            $table->string('uuid')->constrained();
            $table->integer('codprocedencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hospitais', function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('codprocedencia');
        });
    }
}
