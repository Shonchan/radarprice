<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('feed_id');
            $table->string('name', 255);
            $table->decimal('price', 14, 2);
            $table->decimal('cpc', 7, 2);
            $table->integer('currency_id')->default(1);
            $table->string('url', 32)->unique();
            $table->string('hash', 32)->unique();
            $table->string('description', 500);
            $table->string('image', 500);
            $table->tinyInteger('delivery');
            $table->tinyInteger('warranty');
            $table->tinyInteger('pickup');
            $table->tinyInteger('store');
            $table->tinyInteger('stock');
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
        Schema::dropIfExists('offers');
    }
}
