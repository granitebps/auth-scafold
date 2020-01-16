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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => ['auth']], function () {
    Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
        Route::resource('user', 'UserController');
    });

    Route::get('profile', 'ProfileController@edit')->name('profile.edit');
    Route::post('profile', 'ProfileController@update')->name('profile.update');
});
