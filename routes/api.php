<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['middleware' => 'api-auth:api'], function(){
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');

    Route::get('/user', 'UserController@userInfo');
    Route::get('/studentUser', 'UserController@studentUserInfo');

    Route::post('/addTeacher', 'TeachController@addTeacher');
    Route::post('/addSchool', 'TeachController@addSchool');
    Route::post('/addStudent', 'TeachController@addStudent');
    Route::get('/getSchoolsByUserId', 'TeachController@getSchoolsByUserId');

    Route::post('/getTeacherData', 'TeachController@getTeacherData');
    Route::post('/getSchoolData', 'TeachController@getSchoolData');
    Route::post('/getStudentData', 'TeachController@getStudentData');

    Route::get('/getStudentsByUserId', 'TeachController@getStudentsByUserId');
    Route::get('/getTeachersByUserId', 'TeachController@getTeachersByUserId');
    Route::get('/getTeacherAdminsByStudentId', 'TeachController@getTeacherAdminsByStudentId');
    Route::get('/getTeachersDataByStudentId', 'TeachController@getTeachersDataByStudentId');


    Route::post('/getMessageData', 'MessageController@getMessageData');
    Route::post('/updateMessageStatus', 'MessageController@updateMessageStatus');
    Route::post('/addMessage', 'MessageController@addMessage');
    Route::post('/replyMessage', 'MessageController@replyMessage');

    Route::post('/getMessageDataByPusher', 'MessagePusherController@getMessageData');
    Route::post('/updateMessageStatusByPusher', 'MessagePusherController@updateMessageStatus');
    Route::post('/addMessageByPusher', 'MessagePusherController@addMessage');
    Route::post('/replyMessageByPusher', 'MessagePusherController@replyMessage');
    Route::post('/getNotifySumByUserId', 'MessagePusherController@getNotifySumByUserId');


    Route::get('/getFollowDataByUseId', 'TeachController@getFollowDataByUseId');
    Route::get('/getFollowDataByStudentId', 'TeachController@getFollowDataByStudentId');
    Route::post('/follow', 'TeachController@follow');



});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return array(
//        'code'=>200,
//        'data'=>$request->user()
//    );
//});
//
//Route::middleware('auth:student_api')->get('/student_user', function (Request $request) {
//    return array(
//        'code'=>200,
//        'data'=>$request->user()
//    );
//});
Route::post('/register', 'Auth\RegisterController@index');

Route::post('/login', 'Auth\LoginController@login');
Route::post('/studentLogin', 'Auth\LoginController@studentLogin');
Route::post('/logout', 'Auth\LoginController@logout');

Route::post('/lineBind', 'LineController@lineBind')->name('lineBind');

//Route::post('/push/auth', 'MessagePusherController@auth')->name('auth');


