<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('challan_no')->unique();
            $table->unsignedBigInteger('from_warehouse_id');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->foreign('to_warehouse_id')->references('id')->on('warehouses');
            $table->float('item');
            $table->float('total_transfer_qty');
            $table->float('total_receive_qty')->default(0);
            $table->float('total_damage_qty')->default(0);
            $table->double('shipping_cost')->nullable();
            $table->double('grand_total');
            $table->date('transfer_date')->index('transfer_date');
            $table->enum('transfer_status',['1','2'])->comment="1=Transfered,2=Pending";
            $table->unsignedBigInteger('receiver_id');
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->date('receive_date')->nullable();
            $table->enum('receive_status',['1','2','3'])->default('3')->comment="1=Full Received,2=Partial,3=Pending";
            $table->text('note')->nullable();
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
        Schema::dropIfExists('transfers');
    }
}
