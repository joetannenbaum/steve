<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OfflinerLaracastVideosCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'offliner:laracastvideos';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Push Laracast videos via PushBullet';

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
		$laracasts = App::make('Steve\External\Laracasts');

		$this->info( 'Checking Laracasts feed...' );

		$new_lessons = $laracasts->getNewLessons();

		$new_video = FALSE;

		foreach ( $new_lessons as $url )
		{
			$lesson_urls = $laracasts->getFileUrls( $url );

			foreach ( $lesson_urls as $url => $title )
			{
				$record = \OfflinerVideo::firstOrNew([
						'video_source' => 'laracasts',
						'video_id'     => $url,
					]);

				if ( $record->id )
				{
					continue;
				}

				$new_video = TRUE;
				$this->info( 'Logging video <comment>' . $title . '</comment>...' );

				$record->video_title = $title;
				$record->video_url   = $laracasts->getDownloadUrl( $url );

				$record->save();
			}
		}

		if ( !$new_video )
		{
			$this->comment( 'No videos = nothing to do! G\'bye.' );
			die();
		}

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
