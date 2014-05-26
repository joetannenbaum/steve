<?php

namespace Steve\External;

class PushBullet {

	private $client;

	public function __construct( \GuzzleHttp\Client $guzzle )
	{
		$this->client = new $guzzle();
	}

	public function pushFile( $file_name, $file_url, $other_devices = [] )
	{
		$file_info = $this->client->head( $file_url );

		$body = [
			    'device_iden' => getenv( 'push_bullet.htc_one.device_iden' ),
				'type'      => 'file',
				'file_name' => $file_name,
				'file_type' => $file_info->getHeader('content-type'),
				'file_url'  => $file_url,
		    ];

		$res = $this->push( $body );

		foreach ( $other_devices as $device )
		{
			$device_iden = getenv( 'push_bullet.' . $device . '.device_iden' );

			if ( $device_iden )
			{
				$body['device_iden'] = $device_iden;
				$this->push( $body );
			}
		}

		return $res->json();
	}

	private function push( $body )
	{
		return $this->client->post('https://api.pushbullet.com/v2/pushes', [
		    'auth' => [ getenv( 'push_bullet.api_key' ), '' ],
		    'body' => $body,
		]);
	}

}