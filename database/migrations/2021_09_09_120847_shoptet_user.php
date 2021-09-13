<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ShoptetUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shoptet_user', function (Blueprint $table) {
            $table->id();

            $table->string('contact_email', 400)->nullable();
            $table->integer('eshop_id')->unique();
            $table->string('access_token', 1000)->nullable();
            $table->string('eshop_url',1000)->nullable();
            $table->string('scope',1000)->nullable();
            $table->index('eshop_id');

//            $table->foreign('clientId')->references('clientId')->on('shoptet_user_login');

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
        Schema::dropIfExists('shoptet_user');

    }
}
