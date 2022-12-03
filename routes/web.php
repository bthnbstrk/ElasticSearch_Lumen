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

$router->get('/', 'ElasticSearchController@index');
$router->get('/bulk', 'ElasticSearchController@bulk');
$router->get('/insert', 'ElasticSearchController@insert');
$router->get('/find', 'ElasticSearchController@find');
$router->get('/delete/{id}', 'ElasticSearchController@delete');
$router->get('/update/{index}/{id}', 'ElasticSearchController@update');
$router->get('/count/{index}', 'ElasticSearchController@count');
