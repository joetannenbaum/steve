<?php

namespace Steve\External\Media;

abstract class Media {

    protected $media_id;

    protected $media = [];

    protected $error;

    public function __construct($media_id)
    {
        $this->media_id = $media_id;
        $this->media    = $this->getInfo();
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
