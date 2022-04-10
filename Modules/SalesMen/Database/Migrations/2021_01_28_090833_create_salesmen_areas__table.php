<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesmenAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesmen_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salesmen_id');
            $table->foreign('salesmen_id')->references('id')->on('salesmen');
            $table->unsignedBigInteger('area_id');
            $table->foreign('area_id')->references('id')->on('locations');
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
        Schema::dropIfExists('salesmen_areas');
    }
}
