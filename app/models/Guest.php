<?php

class Guest extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;

	protected $fillable = [
        'name',
	];

}
