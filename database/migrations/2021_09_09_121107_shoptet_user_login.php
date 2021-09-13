<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShoptetUserLogin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoptet_user_login', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('password', 1000)->nullable();
            $table->string('address', 10)->nullable();
            $table->integer('eshop_id')->unique();
            $table->index('eshop_id');

            $table->foreign('eshop_id')->references('eshop_id')->on('shoptet_user');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shoptet_user_login');
    }
}
