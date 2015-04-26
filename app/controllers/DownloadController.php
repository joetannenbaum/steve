<?php

class DownloadController extends BaseController {

	public function soundcloud()
	{
		return \App::make('Steve\Downloader\SoundCloud')->download(\Input::get('url'));
	}

}
