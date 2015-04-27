<?php

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CleanUpDownloadedFiles extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'clean:downloaded';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remove old downloaded files.';

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
		$paths       = storage_path('*.{mp3,jpg,zip}');
		$cutoff_time = (new Carbon)->subHour();

		foreach (glob($paths, GLOB_BRACE) as $filename) {
			if (Carbon::createFromTimestamp(filemtime($filename))->lte($cutoff_time)) {
				$this->info('Deleting ' . $filename);
				unlink($filename);
			}
		}
	}

}
