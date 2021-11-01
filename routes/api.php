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
Route::group(['middleware' => ['auth:api']], function(){
    Route::post('/addTeacher', 'EmailController@addTeacher');
    Route::post('/addSchool', 'EmailController@addSchool');
    Route::post('/addStudent', 'EmailController@addStudent');
    Route::get('/getSchoolsByUserId', 'EmailController@getSchoolsByUserId');

    Route::post('/getTeacherData', 'EmailController@getTeacherData');
    Route::post('/getSchoolData', 'EmailController@getSchoolData');
    Route::post('/getStudentData', 'EmailController@getStudentData');

    Route::get('/getStudentsByUserId', 'EmailController@getStudentsByUserId');
    Route::get('/getTeachersByUserId', 'EmailController@getTeachersByUserId');
    Route::get('/getTeacherAdminsByStudentId', 'EmailController@getTeacherAdminsByStudentId');
    Route::get('/getTeachersDataByStudentId', 'EmailController@getTeachersDataByStudentId');


    Route::post('/getMessageData', 'MessageController@getMessageData');
    Route::post('/updateMessageStatus', 'MessageController@updateMessageStatus');
    Route::post('/addMessage', 'MessageController@addMessage');
    Route::post('/replyMessage', 'MessageController@replyMessage');


    Route::get('/getFollowDataByUseId', 'EmailController@getFollowDataByUseId');
    Route::get('/getFollowDataByStudentId', 'EmailController@getFollowDataByStudentId');
    Route::post('/follow', 'EmailController@follow');



});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return array(
        'code'=>200,
        'data'=>$request->user()
    );
});

Route::middleware('auth:student_api')->get('/student_user', function (Request $request) {
    return array(
        'code'=>200,
        'data'=>$request->user()
    );
});
Route::post('/register', 'Auth\RegisterController@index');

Route::post('/login', 'Auth\LoginController@login');
Route::post('/studentLogin', 'Auth\LoginController@studentLogin');

Route::post('/logout', 'Auth\LoginController@logout');
Route::post('/logout2', 'Auth\LoginController@logout2');
Route::post('/lineBind', 'LineController@lineBind')->name('lineBind');



