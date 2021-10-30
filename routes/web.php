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


Route::get('/emailVerify', 'EmailController@emailVerify');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/auth', 'LineController@auth')->name('auth');
Route::get('/success', 'LineController@getSuccess')->name('success');
Route::get('/sessionError', 'LineController@getSessionError')->name('session_error');
Route::get('/loginCancel', 'LineController@getLoginCancel')->name('login_cancel');
Route::get('/login', 'LineController@getLogin')->name('login');
Route::get('/gotoauthpage', 'LineController@gotoauthpage')->name('gotoauthpage');


Route::get('/getUserInfoById', 'UserController@getUserInfoById')->name('getUserInfoById');
Route::get('/getMessageNotifyByReceiveId', 'MessageNotifyController@getMessageNotifyByReceiveId')->name('getMessageNotifyByReceiveId');

//Route::namespace('Auth')->prefix('auth/line')->group(function () {
//    Route::get('/', 'SocialitesLineController@line');
//    Route::get('callback', 'SocialitesLineController@callback');
//});
