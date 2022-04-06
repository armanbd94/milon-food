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
    //District Routes
    Route::get('district', 'DistrictController@index')->name('district');
    Route::group(['prefix' => 'district', 'as'=>'district.'], function () {
        Route::post('datatable-data', 'DistrictController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'DistrictController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'DistrictController@edit')->name('edit');
        Route::post('delete', 'DistrictController@delete')->name('delete');
        Route::post('bulk-delete', 'DistrictController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'DistrictController@change_status')->name('change.status');
    });

    //Upazila Routes
    Route::get('upazila', 'UpazilaController@index')->name('upazila');
    Route::group(['prefix' => 'upazila', 'as'=>'upazila.'], function () {
        Route::post('datatable-data', 'UpazilaController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'UpazilaController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'UpazilaController@edit')->name('edit');
        Route::post('delete', 'UpazilaController@delete')->name('delete');
        Route::post('bulk-delete', 'UpazilaController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'UpazilaController@change_status')->name('change.status');
    });
    Route::get('district-id-wise-upazila-list/{id}','UpazilaController@district_id_wise_upazila_list');

    //Area Route List
    Route::get('area', 'AreaController@index')->name('area');
    Route::group(['prefix' => 'area', 'as'=>'area.'], function () {
        Route::post('datatable-data', 'AreaController@get_datatable_data')->name('datatable.data');
        Route::post('store-or-update', 'AreaController@store_or_update_data')->name('store.or.update');
        Route::post('edit', 'AreaController@edit')->name('edit');
        Route::post('delete', 'AreaController@delete')->name('delete');
        Route::post('bulk-delete', 'AreaController@bulk_delete')->name('bulk.delete');
        Route::post('change-status', 'AreaController@change_status')->name('change.status');
    });
    Route::get('upazila-id-wise-area-list/{id}','AreaController@upazila_id_wise_area_list');


});
