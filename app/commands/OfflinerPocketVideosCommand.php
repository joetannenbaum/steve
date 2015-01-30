<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Carbon\Carbon;
use Steve\External\Pocket;

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

	protected $searches = [
		[
			'params'  => ['contentType' => 'video'],
			'handler' => 'Video',
		],
		[
			'params'  => ['search' => 'soundcloud.com'],
			'handler' => 'SoundCloud',
		],
	];

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
		$users = User::all();

		foreach ($users as $user) {
			$this->getVideos($user);
		}
	}

	protected function getVideos($user)
	{
		$pocket = new Pocket($user->pocket_token);
		$since  = \OfflinerVideo::user($user->id)->max('pocket_since');

		\Log::info('Getting pocket videos since ' .  $since);

		$params = ($since) ? ['since' => $since] : [];

		foreach ($this->searches as $config) {
			$search_params = array_merge($params, $config['params']);

			$this->info('Searching Pocket for ' . json_encode($search_params) . '...');

			$result = $pocket->search($search_params);

			if (empty($result->list)) {
				$this->comment('No results = nothing to do! G\'bye.');
				continue;
			}

			$handler_class = 'Steve\Archiver\\' . $config['handler'];
			$handler = new $handler_class($result, $user);
			$handler->archive();
		}
	}

}
