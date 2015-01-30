<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PHPushbullet\PHPushbullet;
use GuzzleHttp\Client;

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
	protected $description = 'I\'ll check it anyway, so just alert me of changes.';

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
		$client = new Client();

		$params = [
				'headers' => [ 'Authorization' => 'token ' . getenv('github.access_token') ],
			];

		$repos = $client->get('https://api.github.com/user/repos', $params);
		$org_repos = $client->get('https://api.github.com/repos/thephpleague/climate', $params);

		$repos = $repos->json();
		$repos[] = $org_repos->json();

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
			$user   = User::joe()->first();
			$pusher = new PHPushbullet($user->pushbullet_token);

			foreach ($changed as $repo => $changes) {
				foreach ($changes as $key => $value) {
					switch ($key) {
						case 'stargazers_count':
							$emoji = '⭐';
							break;
					}

					$title = $repo;
					$body  = $emoji . ' ' . $value['total'] . ' (+' . $value['delta'] . ')';

					$pusher->channel('joes-github-activity')->link($title, $body, $value['url']);
				}
			}
		}
	}

}
