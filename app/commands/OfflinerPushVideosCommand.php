<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

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
		$pocket = new \Steve\External\Pocket;

		$this->info( 'Checking for unpushed videos...' );

		$video = \OfflinerVideo::whereNull('pusher_id')
								->where('video_source', 'youtube')
								->where('video_error', FALSE)
								->orderBy('id')
								->first();

		if ( empty( $video ) )
		{
			$this->comment( 'No videos = nothing to do! G\'bye.' );
			die();
		}

		$youtube = new \Steve\External\YouTube;
		$pusher  = new \Steve\External\PushBullet;

		$this->info( 'Getting video info for video ID ' . $video->video_id );

		$video_info = $youtube->getVideoInfo( $video->video_id );

		if ( array_get( $video_info, 'title' ) )
		{
			$file_url  = $video_info['best_format']['url'];
			$file_name = $video_info['title'];

			$this->info( 'Pushing ' . $file_name . ' offline...' );

			$push_response = $pusher->pushFile( $file_name, $file_url );

			if ( array_get( $push_response, 'iden' ) )
			{
				$this->info( 'Updating video record video...' );

				$video->video_title = $file_name;
				$video->pusher_id = $push_response['iden'];

			}
			else
			{
				$this->error('Pushing failed: ' . json_encode( $push_response ) );
			}
		}
		else
		{
			$this->error( 'Problem getting video: ' . $video_info['error_message'] );

			$video->video_error = TRUE;
			$video->video_error_message = $video_info['error_message'];
			$video->video_error_code = $video_info['error_code'];

			$notification_message = 'There was a problem getting the following video: '
									. 'https://www.youtube.com/watch?v=' . $video->video_id . '</p>'
									. '<p><strong>' . $video_info['error_message'] . '</strong>';

			\TellEm::error( 'Problem Offlining Video', $notification_message );
		}

		$video->save();

		$this->comment( 'Donezo. Enjoy.' );
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
