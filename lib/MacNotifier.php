<?php

namespace Steve\External;

use Dropbox\Client;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Dropbox as Adapter;

class MacNotifier {

    protected $filesystem;

    public function __construct()
    {
        $client           = new Client(getenv('dropbox.steve_access_token'), 'Steve Jobs');
        $this->filesystem = new Filesystem(new Adapter($client));
    }

    public function notify($title, $body, $url = null, $sender = null)
    {
        $filename     = 'Dev/notifications/' . microtime(TRUE) . '.sh';
        $notification = $this->getNotification($title, $body, $url, $sender);

        $this->filesystem->write($filename, $notification);
    }

    protected function getNotification($title, $body, $url, $sender)
    {
        $args = [
                '-title'   => $title,
                '-message' => $body,
                '-open'    => $url,
                '-sender'  => $sender,
            ];

        $args_str = [];

        foreach ($args as $key => $value) {
            if (!$value) continue;
            $args_str[] = "{$key} \"{$value}\"";
        }

        return 'terminal-notifier ' . implode(' ', $args_str);
    }
}
