<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth']], function () {
    Route::get('production', 'ProductionController@index')->name('production');
    Route::group(['prefix' => 'production', 'as'=>'production.'], function () {
        Route::get('add', 'ProductionController@create')->name('add');
        Route::post('datatable-data', 'ProductionController@get_datatable_data')->name('datatable.data');
        Route::post('check-material-stock', 'ProductionController@check_material_stock')->name('check.material.stock');
        Route::post('store', 'ProductionController@store')->name('store');
        Route::post('update', 'ProductionController@update')->name('update');
        Route::get('edit/{id}', 'ProductionController@edit')->name('edit');
        Route::get('view/{id}', 'ProductionController@show')->name('view');
        Route::post('delete', 'ProductionController@delete')->name('delete');
        Route::post('bulk-delete', 'ProductionController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ProductionController@change_status')->name('change.status');
        
        Route::post('product-materials', 'ProductionController@product_material_list')->name('product.materials');

        Route::get('operation/{id}', 'ProductionOperationController@index')->name('operation');
        Route::post('store-operation', 'ProductionOperationController@store')->name('store.operation');
        Route::post('generate-coupon-qrcode', 'ProductionOperationController@generateCouponQrcode')->name('generate.coupon.qrcode');
        Route::post('change-production-status', 'ProductionOperationController@change_production_status')->name('change.production.status');

        Route::get('transfer/{id}', 'ProductionTransferController@index')->name('transfer');
    });
    Route::get('finish-goods', 'FinishGoodsController@index')->name('finish.goods');
    Route::post('finish-goods/datatable-data', 'FinishGoodsController@get_datatable_data')->name('finish.goods.datatable.data');
});
