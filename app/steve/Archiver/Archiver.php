<?php

namespace Steve\Archiver;

abstract class Archiver {

    protected $result;

    protected $user;

    public function __construct($result, $user)
    {
        $this->result = $result;
        $this->user   = $user;
    }

    abstract public function archive();

    protected function activeResult()
    {
        foreach ($this->result->list as $result) {
            // 0 means not deleted or archived
            if ($result->status == 0) {
                yield $result;
            }
        }
    }

}
