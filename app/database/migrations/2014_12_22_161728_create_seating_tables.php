<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeatingTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('seating', function($table)
		{
		    $table->increments('id');
		    $table->string('name');
		    $table->string('arrangement');
		    $table->timestamps();
		    $table->softDeletes();
		});

		Schema::create('guests', function($table)
		{
		    $table->increments('id');
		    $table->string('name');
		    $table->timestamps();
		    $table->softDeletes();
		});

		Schema::create('seating_tables', function($table)
		{
		    $table->increments('id');
		    $table->string('name');
		    $table->integer('max')->unsigned();
		    $table->integer('top')->unsigned()->nullable();
		    $table->integer('left')->unsigned()->nullable();
		    $table->timestamps();
		    $table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('seating');
		Schema::drop('guests');
		Schema::drop('seating_tables');
	}

}
