<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionProductMaterialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_product_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_product_id');
            $table->foreign('production_product_id')->references('id')->on('production_products');
            $table->unsignedBigInteger('material_id');
            $table->foreign('material_id')->references('id')->on('materials');
            $table->unsignedBigInteger('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->float('qty')->nullable();
            $table->float('cost')->nullable();
            $table->float('total')->nullable();
            $table->float('used_qty')->nullable();
            $table->float('damaged_qty')->nullable();
            $table->float('odd_qty')->nullable();
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
        Schema::dropIfExists('production_product_materials');
    }
}
