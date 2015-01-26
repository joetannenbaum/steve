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
		$users = User::all();

		foreach ($users as $user) {
			$this->getVideos($user);
		}
	}

	protected function getVideos($user)
	{
		$pocket = new \Steve\External\Pocket($user->pocket_token);

		$since = \OfflinerVideo::user($user->id)->max('pocket_since');

		\Log::info('Getting pocket videos since ' .  $since);

		$pocket_params = ($since) ? ['since' => $since] : [];

		$searches = [
			['contentType' => 'video'],
			['search' => 'soundcloud.com'],
		];

		foreach ($searches as $params) {
			$search_params = array_merge($pocket_params, $params);
			$this->searchPocket($pocket, $search_params);
		}
	}

	protected function searchPocket($pocket, $pocket_params)
	{
		$this->info('Searching Pocket for ' . json_encode($pocket_params) . '...');

		$result = $pocket->search($pocket_params);

		if (empty($result->list)) {
			$this->comment('No results = nothing to do! G\'bye.');
			return false;
		}

		foreach ($result->list as $r) {
			// 0 means not deleted or archived
			if ($r->status != 0) {
				continue;
			}

			if (empty($r->videos)) {
				continue;
			}

			foreach ($r->videos as $video) {
				if (empty($video->vid)) {
					continue;
				}

				if (str_contains($video->src, 'youtube')) {
					$src = 'youtube';
				} else if (str_contains($video->src, 'vimeo')) {
					$src = 'vimeo';
				}
//iframe.*src="https:\/\/w\.soundcloud\.com\/player\/\?url=https%3A\/\/api\.soundcloud\.com\/tracks\/(\d+)
				// "kind":"track","id":
				if (!empty($src)) {
					$record = \OfflinerVideo::firstOrNew([
							'video_source' => $src,
							'video_id'     => $video->vid,
						]);

					if ($record->id) {
						continue;
					}

					$this->info('Logging video <comment>' . $video->vid . '</comment>...');

					\Log::info('New pocket since: ' . $result->since);

					$record->fill([
							'pocket_id'    => $r->item_id,
							'pocket_since' => Carbon::createFromTimeStamp($result->since),
							'user_id'      => $user->id,
						]);

					$record->save();
				}
			}
		}

		$this->comment('Donezo. Enjoy.');
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
