<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeedsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feeds', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('filename', 255);
            $table->string('url', 500);
            $table->decimal('cpc', 7, 2)->default(5.0);;
            $table->text('filter');
            $table->integer('rating')->nullable();
            $table->integer('key_field');
            $table->string('logo', 255);
            $table->string('currency', 10)->nullable();
            $table->timestamp('downloaded')->nullable();
            $table->timestamp('updated')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feeds');
    }
}
