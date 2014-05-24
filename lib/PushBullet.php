<?php

namespace Steve\External;

class PushBullet {

	public function pushFile( $file_name, $file_url )
	{
		$client = new \GuzzleHttp\Client();

		$file_info = $client->head( $file_url );

		$res = $client->post('https://api.pushbullet.com/v2/pushes', [
		    'auth' =>  [ getenv( 'push_bullet.api_key' ), '' ],
		    'body' => [
			    'device_iden' => getenv( 'push_bullet.htc_one.device_iden' ),
				'type'      => 'file',
				'file_name' => $file_name,
				'file_type' => $file_info->getHeader('content-type'),
				'file_url'  => $file_url,
		    ]
		]);

		return $res->json();
	}

}