<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs_user', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_log')->nullable();
            $table->string('ip_user')->nullable();
            $table->string('numatendimento')->nullable();
            $table->string('numeroexame')->nullable();
            $table->unsignedBigInteger('uuidatendimento')->nullable();
            $table->unsignedBigInteger('uuidexame')->nullable();

            $table->foreign('id_user')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('id_log')->references('id')->on('logs_action')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('uuidatendimento')->references('uuid')->on('hospitais')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('uuidexame')->references('uuid')->on('hospitais')->onUpdate('NO ACTION')->onDelete('CASCADE');


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
        Schema::dropIfExists('logs_user');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
