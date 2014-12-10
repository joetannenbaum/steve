<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePocketUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function($table)
		{
		    $table->increments('id');
		    $table->string('first_name')->nullable();
		    $table->string('last_name')->nullable();
		    $table->string('pocket_username')->unique()->nullable();
		    $table->string('pocket_token')->unique()->nullable();
		    $table->string('pushbullet_token')->unique()->nullable();
		    $table->timestamps();
		    $table->softDeletes();
		});

		\DB::table('users')->insert([
				'pocket_token'     => getenv('pocket.access_token'),
				'pushbullet_token' => getenv('pushbullet.access_token'),
			]);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('users');
	}

}
