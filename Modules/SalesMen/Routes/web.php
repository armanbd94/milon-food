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
    Route::get('sales-representative', 'SalesMenController@index')->name('sales.representative');
    Route::group(['prefix' => 'sales-representative', 'as'=>'sales.representative.'], function () {
        Route::post('datatable-data', 'SalesMenController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'SalesMenController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'SalesMenController@edit')->name('edit');
        Route::post('view', 'SalesMenController@show')->name('view');
        Route::post('delete', 'SalesMenController@delete')->name('delete');
        Route::post('bulk-delete', 'SalesMenController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'SalesMenController@change_status')->name('change.status');
        Route::post('upazila-route-list', 'SalesMenController@upazila_route_list')->name('upazila.route.list');
        Route::post('daily-route-list', 'SalesMenController@daily_route_list')->name('daily.route.list');
        Route::get('due-amount/{id}', 'SalesMenController@due_amount');
        
    });
    Route::get('warehouse-wise-salesmen-list/{warehouse_id}', 'SalesMenController@warehouse_wise_salesmen_list')->name('warehouse.wise.salesmen.list');

    Route::get('salesmen-ledger', 'SalesmenLedgerController@index')->name('salesmen.ledger');
    Route::post('salesmen-ledger-datatable-data', 'SalesmenLedgerController@get_datatable_data')->name('sales.representative.ledger.datatable.data');
});