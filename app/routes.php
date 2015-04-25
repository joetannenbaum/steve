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

Route::get('download-sc', function() {
    $init_url  = Input::get('url');
    $info      = json_decode(exec("youtube-dl '" . $init_url . "' --print-json --simulate"));
    $track     = json_decode(file_get_contents('http://api.soundcloud.com/tracks/' . $info->display_id . '.json?client_id=1d2f986c5cce33fc3e960088caf6aea7'));
    $artist    = json_decode(file_get_contents('http://api.soundcloud.com/users/' . $track->user->id . '.json?client_id=1d2f986c5cce33fc3e960088caf6aea7'));
    $extension = head(explode('?', pathinfo($info->url, PATHINFO_EXTENSION))) ?: 'mp3';

    $art = $info->thumbnail ?: str_replace('-large.jpg', '-t500x500.jpg', $artist->avatar_url);

    $filepath = storage_path(pathinfo($init_url, PATHINFO_BASENAME) . '.' . $extension);
    $art_path = storage_path(pathinfo($art, PATHINFO_BASENAME));

    file_put_contents($filepath, file_get_contents($info->url));
    file_put_contents($art_path, file_get_contents($art));

    require_once app_path('../lib/getid3/getid3.php');
    require_once app_path('../lib/getid3/write.php');

    $tagwriter                    = new getid3_writetags;
    $tagwriter->filename          = $filepath;
    $tagwriter->tagformats        = ['id3v2.3'];
    $tagwriter->overwrite_tags    = true;
    $tagwriter->tag_encoding      = 'UTF-8';
    $tagwriter->remove_other_tags = true;

    $tag_data['title'][]  = $info->title;
    $tag_data['artist'][] = $artist->full_name;
    $tag_data['year'][]   = date('Y', strtotime($track->created_at));
    $tag_data['genre'][]  = $track->genre;

    if ($fd = @fopen($art_path, 'rb')) {
        $tag_data['attached_picture'] = [
            [
                'data'          => fread($fd, filesize($art_path)),
                'picturetypeid' => 0x03, // 'Cover (front)'
                'description'   => 'Cover',
                'mime'          => 'image/jpeg',
            ]
        ];

        fclose($fd);
    }

    $tagwriter->tag_data = $tag_data;
    $tagwriter->WriteTags();

    return Response::download($filepath);
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

Route::post('/sit-down/add-person', function() {
    $params = Input::only(['name']);

    $guest = Guest::create($params);

    $guest->html = '<a href="#" class="remove">x</a> ' . $guest->name;

    return $guest->toArray();
});
