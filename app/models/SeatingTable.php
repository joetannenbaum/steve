<?php

class SeatingTable extends Eloquent {

    use Illuminate\Database\Eloquent\SoftDeletingTrait;

	protected $fillable = [
        'name',
        'top',
        'right',
        'bottom',
        'left',
        'max',
	];

    public function getPositionAttribute()
    {
        $str = [];

        foreach (['top', 'right', 'bottom', 'left'] as $attr) {
            if ($this->$attr !== null) {
                $str[] = $attr . ':' . $this->$attr . 'px';
            }
        }

        return implode(';', $str);
    }

}
