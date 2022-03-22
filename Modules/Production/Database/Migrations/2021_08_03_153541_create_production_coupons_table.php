<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_product_id');
            $table->foreign('production_product_id')->references('id')->on('production_products');
            $table->string('batch_no',20);
            $table->string('coupon_code',15)->unique();
            $table->enum('status',['1','2'])->default('2')->comment = "1=Used, 2=Not Used";
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
        Schema::dropIfExists('production_coupons');
    }
}
