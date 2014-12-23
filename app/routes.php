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

Route::get('/', function() {
	return View::make('hello');
});

Route::get('/roku-screensaver', function() {
    $photos = \Cache::get( 'roku-screensaver' );

    shuffle( $photos );

    return $photos;
});

Route::get('authorize-offliner', function() {
    $pocket = new \Steve\External\Pocket();

    return Redirect::to($pocket->getAuthUrl());
});

Route::get('pocket-authorized', function() {
    $pocket = new \Steve\External\Pocket();

    $response = $pocket->finishAuth();

    $params   = [
        'pocket_username' => $response['username'],
        'pocket_token'    => $response['access_token'],
    ];

    $user = User::firstOrNew($params);

    if (!$user->id) {
        $user->save();
    }

    Session::put('user', $user);

    return View::make('authorize-pushbullet');
});

Route::post('authorize-pushbullet', function() {
    User::find(Session::get('user')->id)->update(['pushbullet_token' => Input::get('token')]);

    $pusher = new PHPushbullet\PHPushbullet(Input::get('token'));

    return View::make('select-pusher-devices', ['devices' => $pusher->devices()]);
});

Route::post('authorization-done', function() {
    foreach (Input::get('devices', []) as $device) {
        list($pushbullet_id, $name) = explode('|', $device);
        $push_device = PushbulletDevice::firstOrNew([
                'user_id' => Session::get('user')->id,
                'pushbullet_id' => $pushbullet_id,
                'name' => $name,
            ]);

        if (!$push_device->id) {
            $push_device->save();
        }
    }

    return View::make('authorization-done');
});

Route::get('/offliner', function() {
    return View::make('offliner');
});

Route::post('/offliner', function() {
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

Route::get('/sit-down/{id?}', function($id = null) {
    $seating = Seating::orderBy('id', 'desc')->get();

    $current = ($id) ? Seating::find($id) : $seating->first();

    $display = [
        'seating'      => $current,
        'seating_json' => json_encode(($current) ? $current->arrangement : null),
        'guests'       => Guest::orderBy('name')->get(),
        'tables'       => SeatingTable::all(),
        'all_seating'  => $seating,
    ];

    return View::make('seating/index', $display);
});

Route::post('/sit-down', function() {
    $params = Input::only(['name', 'arrangement']);

    $seating = Seating::create($params);

    $seating->html = '<a href="/sit-down/' . $seating->id . '">'
                    . $seating->name
                    . ' (' . $seating->created_at->format('m/d/Y h:iA') . ')</a>';

    return $seating->toArray();
});
