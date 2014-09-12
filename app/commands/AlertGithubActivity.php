<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AlertGithubActivity extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'alert:github-activity';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'I\'ll check it anyway, just alert me of changes.';

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
		$client = new \GuzzleHttp\Client();

		$repos = $client->get('https://api.github.com/user/repos', [
				'headers' => [
					'Authorization' => 'token ' . getenv('github.access_token'),
				],
			]);

		$repos = $repos->json();

		$changed = [];

		foreach ($repos as $repo) {
			$previous = Cache::tags('github-activity')->get($repo['id'], []);

			$new = [];

			foreach (['stargazers_count'] as $key) {

				$new[$key] = $repo[$key];

				if ($repo[$key] > array_get($previous, $key, 0)) {
					$changed[$repo['name']][$key] = [
							'url'   => $repo['html_url'],
							'total' => $repo[$key],
							'delta' => $repo[$key] - array_get($previous, $key, 0),
						];
				}

				Cache::tags('github-activity')->put($repo['id'], $new, 60);
			}
		}

		if ($changed) {
			$pusher = new PHPushbullet\PHPushbullet();

			foreach ($changed as $repo => $changes) {
				foreach ($changes as $key => $value) {
					switch ($key) {
						case 'stargazers_count':
							$emoji = '⭐';
							break;
					}

					$title = $repo . ' ' . $emoji;
					$body  = $value['total'] . ' (+' . $value['delta'] . ')';
					$pusher->device('Chrome')->note($title, $body);
				}
			}
		}
	}

}
