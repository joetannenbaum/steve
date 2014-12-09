<?php

namespace Steve\External;

class Vimeo {

	public function getVideoInfo($video_id)
	{
		$page = file_get_contents('http://player.vimeo.com/video/' . $video_id);
		$json = ltrim(strstr($page, 'a={'), 'a=');
		$json = substr($json, 0, strpos($json, ';if(a.request)'));
		$info = json_decode($json);

		$video_info = [];

		$qualities = ['hd', 'sd', 'mobile'];

		foreach ($qualities as $quality) {
			if ($info->request->files->h264->$quality) {
				$video_info['url'] = $info->request->files->h264->$quality->url;
				break;
			}
		}

		$video_info['title'] = $info->video->title;

	    return $video_info;
	}

}
