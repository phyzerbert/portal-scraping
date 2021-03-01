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
