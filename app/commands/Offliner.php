<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class Offliner extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'offliner';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Get content to the offline folder in Dropbox';

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

		$this->info( 'Getting videos from Pocket...' );

		$result = $pocket->getVideos([
				'count' => 5,
			]);

		if ( empty( $result->list ) )
		{
			$this->info( 'No videos = nothing to do! G\'bye.' );
			die();
		}

		$youtube = new \Steve\External\YouTube;
		$pusher  = new \Steve\External\PushBullet;

		foreach ( $result->list as $r )
		{
			foreach ( $r->videos as $video )
			{
				if ( str_contains( $video->src, 'youtube' ) )
				{
					$this->info( 'Getting video info for video ID' . $video->vid );

					$video_info = $youtube->getVideoInfo( $video->vid );

					$file_url  = $video_info['best_format']['url'];
					$file_name = $video_info['title'];

					$this->info( 'Pushing ' . $file_name . ' offline...' );

					$response = $pusher->pushFile( $file_name, $file_url );

					if ( array_get( $response, 'iden' ) )
					{
						$this->info( 'Logging video...' );

						\OfflinerVideo::create([
								'video_title'  => $file_name,
								'video_source' => 'youtube',
								'video_id'     => $video->vid,
								'pocket_id'    => $r->item_id,
								'pocket_since' => $result->since,
								'pusher_id'    => $response['iden'],
							]);
					}
					else
					{
						dd( $response );
					}
				}
			}
		}

		$this->info( 'Donezo. Enjoy.' );
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
