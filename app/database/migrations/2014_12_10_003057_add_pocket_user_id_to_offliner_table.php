<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPocketUserIdToOfflinerTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('offliner_videos', function($table)
		{
			$table->integer('user_id')->unsigned()->after('id');
		});

		$user = \DB::table('users')->first();
		\DB::table('offliner_videos')->update(['user_id' => $user->id]);

		Schema::table('offliner_videos', function($table)
		{
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
		Schema::table('offliner_videos', function($table)
		{
			$table->dropColumn('user_id');
		});
	}

}
