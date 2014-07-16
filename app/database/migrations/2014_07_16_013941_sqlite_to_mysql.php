<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SqliteToMysql extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$records = DB::connection('sqlite')->table('offliner_videos')->get();

		$records = json_decode( json_encode( $records ), TRUE );

		DB::table('offliner_videos')->insert( $records );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
