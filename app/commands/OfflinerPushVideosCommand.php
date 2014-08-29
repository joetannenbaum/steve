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

		$video = \OfflinerVideo::unpushed()
								->orderBy('id')
								->first();

		if ( empty( $video ) )
		{
			$this->comment( 'No videos = nothing to do! G\'bye.' );
			die();
		}

		$pusher  = new JoeTannenbaum\PHPushbullet\PHPushbullet;

		switch ( $video->video_source )
		{
			case 'youtube':

				$video = $this->handleYouTube( $video );

			break;
		}

		if ( !$video->video_url )
		{
			$this->error('No video URL found, killing it');
			$video->save();
			die();
		}

		$this->info( 'Pushing <comment>' . $video->video_title . '</comment> offline...' );

		$push_response = $pusher->device('HTC One')->file( $video->video_title, $video->video_url );
		$push_response = reset( $push_response );

		if ( array_get( $push_response, 'iden' ) )
		{
			$this->info( 'Updating video record video...' );

			$video->pusher_id = $push_response['iden'];

		}
		else
		{
			$this->error('Pushing failed: ' . json_encode( $push_response ) );
		}

		$video->save();

		$this->comment( 'Donezo. Enjoy.' );
	}

	private function handleYouTube( $video )
	{
		$youtube = new \Steve\External\YouTube;

		$this->info( 'Getting video info for video ID ' . $video->video_id );

		$video_info = $youtube->getVideoInfo( $video->video_id );

		if ( array_get( $video_info, 'title' ) )
		{
			$video->video_url   = $video_info['best_format']['url'];
			$video->video_title = $video_info['title'];
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

		return $video;
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
