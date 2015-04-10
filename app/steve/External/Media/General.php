<?php

namespace Steve\External\Media;

class General extends Media {

	public function getInfo()
	{
		$info = exec("youtube-dl '{$this->source}' --simulate --print-json");
		$info = json_decode($info);

		if ($info === null) {
			$this->error = [
				'error'   => true,
				'message' => 'Invalid video type.',
				'code'    => 500,
			];
		}

		return $info;
	}

	public function fileUrl()
	{
		return $this->media->url;
	}

	public function title()
	{
		return $this->media->title;
	}

	public function webUrl()
	{
		return $this->media->webpage_url;
	}

}
