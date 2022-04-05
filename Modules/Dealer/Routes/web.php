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
    Route::get('dealer', 'DealerController@index')->name('dealer');
    Route::group(['prefix' => 'dealer', 'as'=>'dealer.'], function () {
        Route::post('datatable-data', 'DealerController@get_datatable_data')->name('datatable.data');
        Route::get('add', 'DealerController@create')->name('add');
        Route::post('store-or-update', 'DealerController@store_or_update_data')->name('store.or.update');
        Route::get('edit/{id}', 'DealerController@edit')->name('edit');
        Route::post('view', 'DealerController@show')->name('view');
        Route::post('delete', 'DealerController@delete')->name('delete');
        Route::post('bulk-delete', 'DealerController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DealerController@change_status')->name('change.status');
        Route::get('previous-balance/{id}', 'DealerController@previous_balance');
    });
    Route::post('dealer-list','DealerController@dealer_list');
    Route::post('area-wise-dealer-list', 'DealerController@area_wise_dealer_list')->name('area.wise.dealer.list');
    //dealer Ledger Routes
    Route::get('dealer-ledger', 'DealerLedgerController@index')->name('dealer.ledger');
    Route::post('dealer-ledger/datatable-data', 'DealerLedgerController@get_datatable_data')->name('dealer.ledger.datatable.data');

    //dealer Advance Routes
    Route::get('dealer-advance', 'DealerAdvanceController@index')->name('dealer.advance');
    Route::group(['prefix' => 'dealer-advance', 'as'=>'dealer.advance.'], function () {
        Route::post('datatable-data', 'DealerAdvanceController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DealerAdvanceController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DealerAdvanceController@edit')->name('edit');
        Route::post('view', 'DealerAdvanceController@show')->name('view');
        Route::post('delete', 'DealerAdvanceController@delete')->name('delete');
        Route::post('bulk-delete', 'DealerAdvanceController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DealerAdvanceController@change_status')->name('change.status');
    });

    

});