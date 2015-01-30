<?php

namespace Steve\External\Media;

class SoundCloud extends Media {

	public function getInfo()
	{
        $client = new \GuzzleHttp\Client;

        list($media_id, $url) = explode('|', $this->media_id);

        try {
            $res = $client->get($url);
        } catch (\Exception $e) {
            $this->error = [
                'message' => 'Problem retrieving page.',
                'code'    => 500,
            ];

            return;
        }

        preg_match('/window.__sc_version = "(\w+)"/', (string) $res->getBody(), $matches);\

        if (empty($matches)) {
            $this->error = [
                'message' => 'Problem parsing page.',
                'code'    => 500,
            ];

            return;
        }

        $creds = [
            'client_id'   => 'b45b1aa10f1ac2941910a7f0d10f8e28',
            'app_version' => $matches[1],
        ];

        try {
            $res = $client->get("https://api.soundcloud.com/tracks", [
                    'query' => array_merge(['ids' => $this->media_id], $creds),
                ]]);

            $info = $res->json(['object' => true])[0];

            $res = $client->get("https://api.soundcloud.com/tracks/{$this->media_id}/streams", ['query' => $creds]);
            $files = $res->json(['object' => true]);
        } catch (\Exception $e) {
            $this->error = [
                'message' => $e->getMessage(),
                'code'    => $e->getStatusCode(),
            ];

            return;
        }

        if (empty($files->http_mp3_128_url)) {
        	$this->error = [
				'message' => 'Unable to retrieve file.',
				'code'    => 500,
        	];

            return;
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
