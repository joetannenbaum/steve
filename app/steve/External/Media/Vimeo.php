<?php

namespace Steve\External\Media;

class Vimeo extends Media {

	public function getInfo()
	{
		$config_url = $this->webUrl() . '/config';
		$info       = json_decode(file_get_contents($config_url));

		$video_info = [];
		$qualities  = ['hd', 'sd', 'mobile'];

		foreach ($qualities as $quality) {
			if (!empty($info->request->files->h264->$quality)) {
				$video_info['url'] = $info->request->files->h264->$quality->url;
				break;
			}
		}

		$video_info['title'] = $info->video->title;

	    return $video_info;
	}

	public function fileUrl()
	{
		return $this->media['url'];
	}

	public function title()
	{
		return $this->media['title'];
	}

	public function webUrl()
	{
		return 'http://player.vimeo.com/video/' . $this->media_id;
	}

}
