<?php

class OfflinerVideo extends Eloquent {

	protected $softDelete = true;

	public $timestamps = true;

	protected $fillable = [
		'video_title',
		'video_source',
		'video_id',
		'video_url',
		'user_id',
		'pocket_id',
		'pocket_since',
		'pusher_id',
	];

	public function scopeUnpushed($query)
	{
		return $query->whereNull('pusher_id')->where('video_error', false);
	}

	public function scopeUser($query, $user_id)
	{
		return $query->where('user_id', $user_id);
	}

}
