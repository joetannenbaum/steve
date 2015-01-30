<?php

namespace Steve\External\Media;

class SoundCloud extends Media {

	public function getInfo()
	{
        $client = new \GuzzleHttp\Client;

        $res = $client->get("https://api.soundcloud.com/tracks", ['query' => [
                'ids'         => $this->media_id,
                'client_id'   => 'b45b1aa10f1ac2941910a7f0d10f8e28',
                'app_version' => 'acf910a',
            ]]);

        $info = $res->json(['object' => true])[0];

        $res = $client->get("https://api.soundcloud.com/tracks/{$this->media_id}/streams", ['query' => [
                'client_id'   => 'b45b1aa10f1ac2941910a7f0d10f8e28',
                'app_version' => 'acf910a',
            ]]);

        $files = $res->json(['object' => true]);

        if (empty($files->http_mp3_128_url)) {
        	$this->error = [
				'message' => 'Unable to retrieve file.',
				'code'    => 500,
        	];
        }

        return [
			'title'    => $info->title,
			'file_url' => $files->http_mp3_128_url,
			'web_url'  => $info->permalink_url,
        ];
	}

	public function fileUrl()
	{
		return $this->media['file_url'];
	}

	public function title()
	{
		return $this->media['title'];
	}

	public function webUrl()
	{
		return $this->media['web_url'];
	}

}
