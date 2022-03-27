<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDealersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->string('shop_name',50);
            $table->string('mobile',15);
            $table->string('email',50)->nullable();
            $table->string('image',255)->nullable();
            $table->unsignedBigInteger('district_id');
            $table->foreign('district_id')->references('id')->on('locations');
            $table->unsignedBigInteger('upazila_id');
            $table->foreign('upazila_id')->references('id')->on('locations');
            $table->string('address',255)->nullable();
            $table->enum('status',['1','2'])->default('1')->comment = "1=Active, 2=Inactive";
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
        Schema::dropIfExists('dealers');
    }
}
