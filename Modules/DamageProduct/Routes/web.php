<?php

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
    Route::get('damage', 'DamageController@index')->name('damage');
    Route::group(['prefix' => 'damage', 'as'=>'damage.'], function () {
        Route::get('sale', 'DamageController@damage_sale')->name('sale');
    });
   
    //Damage Product Routes
    Route::get('damage-product', 'DamageProductController@index')->name('damage.product');
    Route::group(['prefix' => 'damage-product', 'as'=>'damage.product.'], function () {
        Route::post('datatable-data', 'DamageProductController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'DamageProductController@store')->name('store');
        Route::get('{id}/show', 'DamageProductController@show')->name('show');
        Route::post('delete', 'DamageProductController@delete')->name('delete');
        Route::post('bulk-delete', 'DamageProductController@bulk_delete')->name('bulk.delete');
        Route::post('product-search-with-id-for-damage', 'DamageProductController@product_search_with_id_for_damage')->name('search.with.id.for.damage');
    });
       
});
