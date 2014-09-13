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

    public function notify($title, $body, $url, $sender)
    {
        $filename     = 'Dev/notifications/' . microtime(TRUE) . '.sh';
        $notification = $this->getNotification($title, $body, $url, $sender);

        $this->filesystem->write($filename, $notification);
    }

	protected function getNotification($title, $body, $url)
	{
		return 'sleep 3 ' . "\n"
				. 'terminal-notifier -message "' . $body . '" '
				. '-title "' . $title . '" '
				. '-open ' . $url . ' '
				. '-sender com.github.GitHub';
	}

}
