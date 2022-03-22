<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->foreign('production_id')->references('id')->on('productions');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->integer('year');
            $table->date('mfg_date');
            $table->date('exp_date');
            $table->enum('has_coupon',['1','2'])->comment = "1=Yes, 2=No";
            $table->float('total_coupon')->nullable();
            $table->float('coupon_price')->nullable();
            $table->date('coupon_exp_date')->nullable();
            $table->double('base_unit_qty')->nullable();
            $table->double('per_unit_cost')->nullable();
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
        Schema::dropIfExists('production_products');
    }
}
