<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableDomainsHospitals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains_hospitals', function (Blueprint $table) {
            $table->unsignedBigInteger('codprocedencia')->references('codprocedencia')->on('hospitais')->onUpdate('NO ACTION')->onDelete('CASCADE')->nullable();
            $table->string('domains');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('domains_hospitals');
    }
}
