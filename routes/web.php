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

Route::get('/', 'CompanyController@getCompanies');
Route::get('/download_images', 'CompanyController@downloadImages');
Route::get('/check_duplicates', 'CompanyController@checkDuplicates');
Route::get('/get_attributes', 'CompanyController@getAttributes');

Route::get('/solve_closed', 'CompanyController@solveClosed');
Route::get('/remove_null_address', 'CompanyController@removeNullAddress');
Route::get('/change_business_time', 'CompanyController@changeBusinessTime');

Route::get('/brand/get_brands', 'BrandController@getBrands');
Route::get('/brand/download_images', 'BrandController@downloadImages');
Route::get('/brand/check_duplicates', 'BrandController@checkDuplicates');
Route::get('/brand/get_attributes', 'BrandController@getAttributes');
