<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesmenProductTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesmen_product_targets', function (Blueprint $table) {
            $table->id();
            $table->string('ptcode')->unique();
            $table->unsignedBigInteger('salesmen_id');
            $table->foreign('salesmen_id')->references('id')->on('salesmen');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');
            $table->double('target_value');
            $table->double('achieved_value')->nullable();
            $table->float('commission_rate');
            $table->float('commission_earned');
            $table->integer('target_month');
            $table->date('closing_date');
            $table->string('created_by')->nullable();
            $table->string('modified_by')->nullable();
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
        Schema::dropIfExists('salesmen_product_targets');
    }
}
