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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/lxpdataingest', 'testController@lxpdataingest');
Route::get('/redirect', 'testController@redirect');

Route::get('/create', 'createController@create');
Route::get('/parse', 'testController@parseUrl');

Route::get('/casejson', 'createTaxonomyController@createTaxonomyCaseJson');
Route::get('/auth', 'createTaxonomyController@checkAuth');
Route::get('/publish', 'createTaxonomyController@publishTaxonomy');
Route::get('/checkPublish', 'createTaxonomyController@checkPublishTaxonomy');
Route::get('/copyToLor', 'createTaxonomyController@copyTaxonomyToLOR');


Route::get('/createexcel', 'createExcelController@create');