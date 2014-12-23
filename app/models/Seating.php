<?php

class Seating extends Eloquent {

	use Illuminate\Database\Eloquent\SoftDeletingTrait;

    protected $table = 'seating';

	protected $fillable = [
        'name',
        'arrangement',
	];

    protected function getArrangementAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return unserialize($value);
    }

    protected function setArrangementAttribute($value)
    {
        $this->attributes['arrangement'] = serialize($value);
    }

}
