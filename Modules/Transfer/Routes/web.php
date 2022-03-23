<?php
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::get('transfer-inventory', 'TransferController@index')->name('transfer.inventory');
    Route::group(['prefix' => 'transfer-inventory', 'as'=>'transfer.inventory.'], function () {
        Route::get('create', 'TransferController@create')->name('create');
        Route::post('datatable-data', 'TransferController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'TransferController@store_or_update')->name('store.update');
        Route::post('receive', 'TransferController@receive_transfered_products')->name('receive');
        Route::get('edit/{id}', 'TransferController@edit')->name('edit');
        Route::get('view/{id}', 'TransferController@show')->name('view');
        Route::post('delete', 'TransferController@delete')->name('delete');
        Route::post('bulk-delete', 'TransferController@bulk_delete')->name('bulk.delete');
        Route::get('data/{id}', 'TransferController@transfer_product_data')->name('data');
    });
});
