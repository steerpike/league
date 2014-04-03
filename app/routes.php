<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/
use LeagueWrap\Api;
Route::get('summoner', function()
{
	return "Summoner";
});

Route::get('/search', function()
{
	return View::make('search');
});

Route::post('/search', 'SearchController@searchSummoner');

Route::get('/{region}', function()
{
	return "region";
});

Route::get('/{region}/{summoner}', function()
{
	//Create google spreadsheet and fill with latest info
	return "summoner by region";
});
Route::get('/', function()
{
	//login to google+
	return View::make('hello');
});