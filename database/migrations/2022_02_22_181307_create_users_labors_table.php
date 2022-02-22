<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersLaborsTable extends Migration
{

    /**
     * Schema table name to migrate
     * @var string
     */
    public $tableName = 'users_labors';

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
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_labor');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onUpdate('NO ACTION')->onDelete('CASCADE');
            $table->foreign('id_labor')->references('id')->on('labors')->onUpdate('NO ACTION')->onDelete('CASCADE');
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
