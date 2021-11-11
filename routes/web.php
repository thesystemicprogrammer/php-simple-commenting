<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'api/public'], function () use ($router) {
    $router->post('/comment', 'CommentController@createComment');
    $router->get('/comment/approve/{approval_hash}', 'CommentController@approveWithHash');
});

$router->group(['prefix' => 'api/admin', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/comment/{pageHash}', 'CommentController@getComments');
    $router->get('/comment/approve/{comment_id}', 'CommentController@approve');
    $router->get('/comment/decline/{comment_id}', 'CommentController@decline');
    $router->delete('/comment', 'CommentController@deleteComment');
});
