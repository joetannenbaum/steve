<?php

namespace Steve\External\Media;

class YouTube extends Media {

	public function getInfo()
	{
		$tmp  = $this->curlInfo();
		$info = $this->parseInfo($tmp);

	    if (array_get($info, 'error')) {
	    	$this->error = $info;
	    	$tmp = $this->curlInfo(true);

	    	return $this->parseInfo($tmp);
	    }

	    return $info;
	}

	public function fileUrl()
	{
		return $this->media['best_format']['url'];
	}

	public function title()
	{
		return $this->media['title'];
	}

	public function webUrl()
	{
		return 'https://www.youtube.com/watch?v=' . $this->media_id;
	}

	protected function curlInfo($full = false)
	{
		$params = [
			'video_id' => $this->media_id,
		];

		if ($full) {
			$params = array_merge($params, [
				'asv' => '3',
				'el'  => 'detailpage',
				'hl'  => 'en_US',
			]);
		}

		$url = 'http://www.youtube.com/get_video_info?' . http_build_query($params);

		$ch = curl_init();
	    curl_setopt($ch , CURLOPT_URL , $url);
	    curl_setopt($ch , CURLOPT_RETURNTRANSFER , 1);
	    curl_setopt($ch , CURLOPT_CONNECTTIMEOUT , 3);
	    $tmp = curl_exec($ch);
	    curl_close($ch);

	    return $tmp;
	}
	protected function parseInfo($garbage)
	{
	    parse_str($garbage, $video_info);

	    if (array_get($video_info, 'url_encoded_fmt_stream_map')) {
	    	$tmp_formats = explode(',', $video_info['url_encoded_fmt_stream_map']);

	    	$formats = [];

	    	foreach($tmp_formats as $tf) {
	    		parse_str($tf, $form);

	    		$form['url'] = urldecode($form['url']);

	    		parse_str($form['url'], $form['url_attr']);

	    		$formats[] = $form;
	    	}

			$video_info['formats']     = $formats;
			$video_info['best_format'] = head($formats);
	    } else {
	    	return [
					'error'   => true,
					'message' => array_get($video_info, 'reason'),
					'code'    => array_get($video_info, 'errorcode'),
	    		];
	    }

	    return $video_info;
	}

}
