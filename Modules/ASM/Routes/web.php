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
    Route::get('assistant-sales-manager', 'ASMController@index')->name('asm');
    Route::group(['prefix' => 'assistant-sales-manager', 'as'=>'asm.'], function () {
        Route::post('datatable-data', 'ASMController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'ASMController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'ASMController@edit')->name('edit');
        Route::post('view', 'ASMController@show')->name('view');
        Route::post('delete', 'ASMController@delete')->name('delete');
        Route::post('bulk-delete', 'ASMController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'ASMController@change_status')->name('change.status');
        Route::get('permission/{id}', 'ASMController@permission')->name('permission');
        Route::post('permission/store', 'ASMController@permission_store')->name('permission.store');
    });
    Route::get('district-id-wise-asm-list/{id}','ASMController@district_id_wise_asm_list');
});
