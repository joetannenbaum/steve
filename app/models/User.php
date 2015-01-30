<?php

class User extends Eloquent {

	protected $softDelete = true;

	public $timestamps = true;

	protected $fillable = [
        'first_name',
        'last_name',
		'pocket_username',
        'pocket_token',
		'pushbullet_token',
	];

    public function devices()
    {
        return $this->hasMany('PushbulletDevice');
    }

    public function scopeJoe($query)
    {
        return $query->where('pocket_username', 'jtannenbaum');
    }

}
