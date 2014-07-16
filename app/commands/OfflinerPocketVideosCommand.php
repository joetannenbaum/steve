<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Carbon\Carbon;

class OfflinerPocketVideosCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'offliner:pocketvideos';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scrape Pocket for any videos saved';

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

		$since = \OfflinerVideo::max('pocket_since');

		\Log::info( 'Getting pocket videos since ' .  $since );

		if ( $since )
		{
			$pocket_params = [
				'since' => $since,
			];

			$since = ( is_int( $since ) ) ? $since : strtotime( $since );

			$since_des = date( 'm/d/Y h:i:s A', $since );
		}
		else
		{
			$pocket_params = [];

			$since_des = 'the beginning of time';
		}

		$this->info( 'Getting videos from Pocket since ' . $since_des . '...' );

		$result = $pocket->getVideos( $pocket_params );

		if ( empty( $result->list ) )
		{
			$this->comment( 'No videos = nothing to do! G\'bye.' );
			die();
		}

		foreach ( $result->list as $r )
		{
			// 0 means not deleted or archived
			if ( $r->status != 0 )
			{
				continue;
			}

			if ( empty( $r->videos ) )
			{
				continue;
			}

			foreach ( $r->videos as $video )
			{
				if ( str_contains( $video->src, 'youtube' ) && !empty( $video->vid ) )
				{
					$record = \OfflinerVideo::firstOrNew([
							'video_source' => 'youtube',
							'video_id'     => $video->vid,
						]);

					if ( $record->id )
					{
						continue;
					}

					$this->info( 'Logging video <comment>' . $video->vid . '</comment>...' );

					\Log::info( 'New pocket since: ' . $result->since );

					$record->fill([
							'pocket_id'    => $r->item_id,
							'pocket_since' => new Carbon( $result->since ),
						]);

					$record->save();
				}
			}
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
