<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHospitaisTable extends Migration
{

    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'hospitais';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('cnpj')->nullable();
            $table->string('phone')->nullable();
            $table->string('image')->nullable();

            $table->unsignedBigInteger('grupo_id')->default(1);
            $table->foreign('grupo_id')->references('id')->on('groups')->onDelete('CASCADE');
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
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::dropIfExists($this->tableName);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
