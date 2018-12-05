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
use Intervention\Image\ImageManagerStatic as Image;
use \Illuminate\Http\Request;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', function ()
{
    dd(Storage::disk('media')->getDriver()->getAdapter()->getPathPrefix());
    return view('test');
});

Route::post('/test', function (Request $request)
{

    dd(Image::make($request->avatar));
    dd($request->all());
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
