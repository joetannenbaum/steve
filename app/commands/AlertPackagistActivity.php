<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use PHPushbullet\PHPushbullet;
use GuzzleHttp\Client;

class AlertPackagistActivity extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'alert:packagist-activity';

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
		$user = User::joe()->first();
		$pusher = new PHPushbullet($user->pushbullet_token);
		$client   = new Client();

		$packages = [
				'league/climate',
				'joetannenbaum/mr-clean',
				'joetannenbaum/phpushbullet',
			];

		$base_url = 'https://packagist.org/packages/';

		foreach ($packages as $package) {
			$stats = $client->get($base_url . $package . '.json');
			$stats = $stats->json();

			$key = $stats['package']['name'];
			$previous = Cache::tags('package-activity')->get($key, []);

			$downloads = array_get($previous, 'downloads', []);
			$delta = $stats['package']['downloads']['total'] - array_get($downloads, 'total', 0);

			if ($delta > 0) {
				$body  = "{$stats['package']['downloads']['total']} (+{$delta})\n";
				$body .= "{$stats['package']['downloads']['daily']} today, ";
				$body .= "{$stats['package']['downloads']['monthly']} this month";

				$this->info('Notifying packagist activity: ' . $package);

				$pusher->channel('joes-packagist-activity')->link($package, $body, $base_url . $package);

				Cache::tags('package-activity')->put($key, ['downloads' => ['total' => $stats['package']['downloads']['total']]], 600);
			}

		}

	}

}
