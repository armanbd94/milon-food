<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMaterialPurchaseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('material_purchase', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->unsignedBigInteger('material_id');
            $table->foreign('material_id')->references('id')->on('materials');
            $table->double('qty');
            $table->double('received');
            $table->unsignedBigInteger('purchase_unit_id')->nullable();
            $table->foreign('purchase_unit_id')->references('id')->on('units');
            $table->double('net_unit_cost');
            $table->double('new_unit_cost');
            $table->double('old_cost');
            $table->double('discount');
            $table->double('tax_rate');
            $table->double('tax');
            $table->double('labor_cost')->nullable();
            $table->double('total');
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
        Schema::dropIfExists('material_purchase');
    }
}
