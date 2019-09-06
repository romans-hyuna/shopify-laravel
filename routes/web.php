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

Route::get('/', 'HomeController@index')->name('index');

Route::get('shopify/redirect', 'ShopifyController@redirect')->name('shopify.redirect');
Route::get('shopify/callback', 'ShopifyController@callback');
Route::post('shopify/webhook', 'ShopifyController@webhook');

