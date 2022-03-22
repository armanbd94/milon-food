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
    //Product Routes
    Route::get('product', 'ProductController@index')->name('product');
    Route::group(['prefix' => 'product', 'as'=>'product.'], function () {
        Route::get('add', 'ProductController@create')->name('add');
        Route::post('datatable-data', 'ProductController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ProductController@store_or_update')->name('store.or.update');
        Route::get('edit/{id}', 'ProductController@edit')->name('edit');
        Route::get('view/{id}', 'ProductController@show');
        Route::post('delete', 'ProductController@delete')->name('delete');
        Route::post('bulk-delete', 'ProductController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ProductController@change_status')->name('change.status');
        Route::get('generate-code', 'ProductController@generateProductCode')->name('generate.code');
    });

    Route::post('barcode/product-autocomplete-search', 'BarcodeController@autocomplete_search_product');
    Route::post('barcode/search-product', 'BarcodeController@search_product')->name('barcode.search.product');
    Route::get('print-barcode', 'BarcodeController@index')->name('print.barcode');
    Route::post('generate-barcode', 'BarcodeController@generateBarcode')->name('generate.barcode');


    //Adjustment Routes
    Route::get('adjustment', 'AdjustmentController@index')->name('adjustment');
    Route::group(['prefix' => 'adjustment', 'as'=>'adjustment.'], function () {
        Route::get('add', 'AdjustmentController@create')->name('add');
        Route::post('datatable-data', 'AdjustmentController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'AdjustmentController@store')->name('store');
        Route::post('update', 'AdjustmentController@update')->name('update');
        Route::get('edit/{id}', 'AdjustmentController@edit')->name('edit');
        Route::get('view/{id}', 'AdjustmentController@show')->name('view');
        Route::post('delete', 'AdjustmentController@delete')->name('delete');
        Route::post('bulk-delete', 'AdjustmentController@bulk_delete')->name('bulk.delete');
        
    });

});
