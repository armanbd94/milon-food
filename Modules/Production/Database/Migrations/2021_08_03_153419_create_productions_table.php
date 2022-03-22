<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->unsignedBigInteger('warehouse_id');
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('item')->nullable();
            $table->enum('status',['1','2'])->default('1')->comment = "1=Approved, 2=Not Approved";
            $table->enum('production_status',['1','2','3'])->default('1')->comment = "1=Pending,2=Processing,3=Finished";
            $table->enum('transfer_status',['1','2'])->default('1')->comment = "1=Pending,2=Complete";
            $table->date('transfer_date')->nullable();
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
        Schema::dropIfExists('productions');
    }
}
