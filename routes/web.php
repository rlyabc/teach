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
    View::addExtension('html','php');
    return view()->file(public_path().'/index.html');
});

Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
Route::get('/emailVerify', 'TeachController@emailVerify');

Auth::routes();

Route::get('/auth', 'LineController@auth')->name('auth');
Route::get('/line', 'LineController@getSuccess')->name('success');
Route::get('/sessionError', 'LineController@getSessionError')->name('session_error');
Route::get('/loginCancel', 'LineController@getLoginCancel')->name('login_cancel');
Route::get('/login', 'LineController@getLogin')->name('login');

Route::get('/gotoauthpage', 'LineController@gotoauthpage')->name('gotoauthpage');

Route::get('/getUserInfoById', 'UserController@getUserInfoById')->name('getUserInfoById');
Route::get('/getMessageNotifyByReceiveId', 'MessageController@getMessageNotifyByReceiveId')->name('getMessageNotifyByReceiveId');


Route::post('/message/callback', 'LineController@messageCallback')->name('messageCallback');
Route::get('/getAudienceGroupId', 'LineController@getAudienceGroupId')->name('getAudienceGroupId');
