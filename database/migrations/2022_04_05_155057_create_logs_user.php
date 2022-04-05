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
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->text('Log');
            $table->foreign('id_user')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');

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
        Schema::dropIfExists('logs_user');
    }
}
