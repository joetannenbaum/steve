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

        $client = new \GuzzleHttp\Client;
        $result = $client->get('http://www.youtube.com/get_video_info', ['query' => $params]);

	    return (string) $result->getBody();
	}
	protected function parseInfo($garbage)
	{
	    parse_str($garbage, $video_info);

	    if (!array_get($video_info, 'url_encoded_fmt_stream_map')) {
	    	return [
					'error'   => true,
					'message' => array_get($video_info, 'reason'),
					'code'    => array_get($video_info, 'errorcode'),
	    		];
    	}

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

	    return $video_info;
	}

}
