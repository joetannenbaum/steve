<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfflinerVideos extends Migration {

	private $table = 'offliner_videos';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create( $this->table, function($table)
		{
		    $table->increments('id');
		    $table->string('video_title')->nullable();
		    $table->string('video_source');
		    $table->string('video_id');
		    $table->string('pocket_id');
		    $table->timestamp('pocket_since');
		    $table->string('pusher_id')->nullable();
		    $table->boolean('video_error')->default(FALSE);
		    $table->string('video_error_message')->nullable();
		    $table->integer('video_error_code')->nullable();
		    $table->timestamps();
		    $table->softDeletes();

		    $table->unique([ 'video_source', 'video_id' ]);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop( $this->table );
	}

}
