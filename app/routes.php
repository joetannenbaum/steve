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

Route::get('/', function()
{
	return View::make('hello');
});


Route::get('/roku-screensaver', function()
{
    $photos = \Cache::get( 'roku-screensaver' );

    shuffle( $photos );

    return $photos;
});

Route::get('/offliner', function()
{
    return View::make('offliner');
});

Route::post('/offliner', function()
{
    if (Input::get('password') == getenv('offliner_password')) {
        OfflinerVideo::create([
                'video_title'  => Input::get('title'),
                'video_source' => 'manual',
                'video_id'     => md5(Input::get('url') . time()),
                'video_url'    => Input::get('url'),
            ]);

        return Redirect::to('offliner')->with('success_message', 'Nailed it.');
    }

    return Redirect::to('offliner')->with('error_message', 'Nope. No good.');
});
