<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushbulletUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('pushbullet_devices', function($table)
		{
		    $table->increments('id');
		    $table->integer('user_id')->unsigned();
		    $table->string('name');
		    $table->string('pushbullet_id');
		    $table->timestamps();
		    $table->softDeletes();

		    $table->unique(['user_id', 'pushbullet_id']);

			$table->foreign('user_id')
			      ->references('id')->on('users')
			      ->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('pushbullet_devices');
	}

}
