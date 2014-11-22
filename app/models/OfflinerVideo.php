<?php

class OfflinerVideo extends Eloquent {

	protected $softDelete = TRUE;

	public $timestamps = TRUE;

	protected $fillable = [
		'video_title',
		'video_source',
		'video_id',
		'video_url',
		'pocket_id',
		'pocket_since',
		'pusher_id',
	];

	public function scopeUnpushed( $query )
	{
		return $query->whereNull('pusher_id')->where('video_error', FALSE);
	}

}
