<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class EmailRandomWorkout extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'email:random-workout';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email Joe a random workout.';

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
		$exercises = [
			[
				'Push Ups',
				'Bench Press',
				'Bottoms Up Clean to Press (Kettlebell)',
				'Bicep Curls',
			],
			[
				'Goblet Squats',
				'Deadlift',
				'One Leg Tip Over',
			],
			[
				'Swings',
				'Battle Ropes',
				'Pull Up',
				'Burpies',
			],
			[
				'Suspended Sit Ups',
				'Leg Lifts',
				'Straight Arm Sit Up',
				'Sit Ups With Weight',
			],
			[
				'Side Plank',
				'Plank',
				'Mountain Climbers',
				'Cross Body Elbow To Knee',
				'Plank With Workout Ball',
			],
			[
				'Bent Over Row',
				'Push Up Position Rows',
				'Bent Over Chicken Wings',
				'Bat Wings',
			],
			[
				'Front Lunge',
				'Back Lunge',
				'Prisoner Squat',
				'Step Up',
			],
		];

		$tmp        = [];
		$workout    = [];

		foreach ( $exercises as $set )
		{
			shuffle( $set );
			$ex = array_slice( $set, 0, 2 );

			foreach ( $ex as $key => $e )
			{
				$tmp[ $key ][] = $e;
			}
		}

		foreach ( $tmp as $t )
		{
			foreach ( $t as $e )
			{
				$workout[] = $e;
			}
		}

		$data = [ 'workout' => $workout ];

		Mail::send( 'emails/workout/list', $data, function ( $message )
		{
			$message->from('trainer@steve.joe.codes', 'Personal Trainer');
			$message->to('josephtannenbaum+personaltrainer@gmail.com');
			$message->subject( 'Hey Joe, here is your workout for ' . date('F jS, Y') );
		});
	}

}
