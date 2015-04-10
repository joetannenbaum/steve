<?php

namespace Steve\External\Media;

abstract class Media {

    protected $source;

    protected $media = [];

    protected $error;

    public function __construct($source)
    {
        $this->source = $source;
        $this->media  = $this->getInfo();
    }

    abstract public function webUrl();

    abstract public function title();

    abstract public function fileUrl();

    abstract public function getInfo();

    public function error()
    {
        return !empty($this->error);
    }

    public function errorMessage()
    {
        return array_get($this->error, 'message');
    }

    public function errorCode()
    {
        return array_get($this->error, 'code');
    }

}
