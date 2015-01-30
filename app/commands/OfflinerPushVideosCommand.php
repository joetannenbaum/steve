<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Steve\Notify\MacNotifier;
use PHPushbullet\PHPushbullet;
use Steve\External\Media;

class OfflinerPushVideosCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'offliner:pushvideos';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Push videos via PushBullet';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info('Checking for unpushed videos...');

		$video = \OfflinerVideo::unpushed()
								->orderBy('id')
								->first();

		if (empty($video)) {
			$this->comment('No videos = nothing to do! G\'bye.');
			die();
		}

		if ($video->video_url) {
			$this->push($video);
			return;
		}

		$this->info('Getting video info for video ID ' . $video->video_id);

		$handler = $this->getHandler($video->video_source);

		if (!$handler) {
			$notifier = new MacNotifier();
			$notifier->notify('Unsupported video source, quitting: ' . $video->video_source, null, null, 'com.apple.Automator');

			$this->error('Unsupported video source, quitting: ' . $video->video_source);
		}

		$media = new $handler($video->video_id);

		$video->video_title = $media->title();

		if ($media->error()) {
			$video->video_error         = true;
			$video->video_error_message = $media->errorMessage();
			$video->video_error_code    = $media->errorCode();
			$video->save();

			$this->notifyOfVideoError($video, $media);
			$this->error('Error, quitting: ' . $media->errorMessage());
			die();
		}

		$video->video_url = $media->fileUrl();

		$this->push($video);
	}

	protected function notifyOfVideoError($video, $media)
	{
		$user   = \User::find($video->user_id);
		$pusher = new PHPushbullet($user->pushbullet_token);

		foreach ($user->devices as $device) {
			$pusher->device($device->pushbullet_id)
					->link('Error Pushing Video: ' . $media->title(), $media->webUrl(), $media->errorMessage());
		}
	}

	protected function push($video)
	{
		$user   = \User::find($video->user_id);
		$pusher = new PHPushbullet($user->pushbullet_token);

		$this->info('Pushing <comment>' . $video->video_title . '</comment> offline...');

		foreach ($user->devices as $device) {
			$push_response = $pusher->device($device->pushbullet_id)
									->file($video->video_title, $video->video_url);
			$push_response = reset($push_response);
		}

		if ($video->video_source == 'laracasts') {
			foreach ($_ENV as $key => $value) {
				if (starts_with($key, 'laracasts.email')) {
					$pusher->user($value)->file($video->video_title, $video->video_url);
				}
			}
		}

		if (array_get($push_response, 'iden')) {
			$this->info('Updating video record video...');

			$video->pusher_id = $push_response['iden'];
		} else {
			$notifier = new MacNotifier();
			$title    = 'Pushing Failed';
			$url      = 'http://steve.joe.codes';
			$notifier->notify($title, json_encode($push_response), $url, 'com.apple.Automator');

			$this->error($title . json_encode($push_response));
		}

		$video->save();

		$this->comment('Donezo. Enjoy.');
	}

	protected function getHandler($source)
	{
		switch ($source) {
			case 'youtube':
				return 'Steve\External\Media\YouTube';
			break;

			case 'vimeo':
				return 'Steve\External\Media\Vimeo';
			break;
		}

		return false;
	}

}
