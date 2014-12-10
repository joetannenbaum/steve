<?php

class PushbulletDevice extends Eloquent {

	protected $softDelete = true;

	public $timestamps = true;

	protected $fillable = [
        'user_id',
        'pushbullet_id',
        'name',
    ];

}
