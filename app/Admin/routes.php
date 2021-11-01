<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
//    $router->get('users', 'UsersController@index');
//    $router->get('users/{id}/edit','UsersController@edit');
//    $router->patch('users/{id}','UsersController@update');
//    $router->get('students', 'StudentsController@index');
    Route::resource('users', 'UsersController');
    Route::resource('students', 'StudentsController');
    Route::resource('schools', 'SchoolsController');
});
