<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterOfflineVideoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( 'offliner_videos_new', function($table)
		{
		    $table->increments('id');
		    $table->string('video_title')->nullable();
		    $table->string('video_source');
		    $table->string('video_id')->nullable();
		    $table->string('video_url')->nullable();
		    $table->string('pocket_id')->nullable();
		    $table->timestamp('pocket_since')->nullable();
		    $table->string('pusher_id')->nullable();
		    $table->boolean('video_error')->default(FALSE);
		    $table->string('video_error_message')->nullable();
		    $table->integer('video_error_code')->nullable();
		    $table->timestamps();
		    $table->softDeletes();

		    $table->unique([ 'video_source', 'video_id', 'video_url' ], 'offliner_videos_source_id_url_unique');
		});

		Eloquent::unguard();

		$current = OfflinerVideo::all()->toArray();

		foreach ( $current as $c )
		{
			DB::table('offliner_videos_new')->insert( $c );
		}

		Eloquent::reguard();

		Schema::drop('offliner_videos');
		Schema::rename('offliner_videos_new', 'offliner_videos');
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
