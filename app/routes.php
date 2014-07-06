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
	$cache_key = 'roku-screensaver';

	$return_photos = \Cache::get( $cache_key );
$return_photos = NULL;

	if ( !$return_photos )
	{
		$client = new \GuzzleHttp\Client(['base_url' => 'https://api.dropbox.com/1/']);

		$response = $client->get('metadata/auto/Photos%2FRoku%20Screensaver', [
				'headers' => [
					'Authorization' => 'Bearer ' . getenv('dropbox.access_token'),
				],
				'query' => [
					'locale'     => '',
					'list'       => TRUE,
					'file_limit' => 25000,
				]
			]);

		$files = $response->json();

		$photos = $files['contents'];

		$return_photos = [];

		foreach ( $photos as $p )
		{
			$response = $client->get('media/dropbox' . $p['path'], [
					'headers' => [
						'Authorization' => 'Bearer ' . getenv('dropbox.access_token'),
					],
				]);

			$photo = $response->json();

			$new_file_path = 'images/roku-screensaver/' . last( explode( '/', $photo['url'] ) );

			file_put_contents( public_path( $new_file_path ), file_get_contents( $photo['url'] ) );

			$photo_info = getimagesize( public_path( $new_file_path ) );

			$return_photos[] = [
					'url'    => asset( $new_file_path ),
					'width'  => $photo_info[ 0 ],
					'height' => $photo_info[ 1 ],
				];
		}

		\Cache::put( $cache_key, $return_photos, \Carbon\Carbon::now()->addHours(4) );
	}

	shuffle( $return_photos );

	return $return_photos;
});