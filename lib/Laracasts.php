<?php

namespace Steve\External;

class Laracasts {

	private $client;

	public function __construct( \GuzzleHttp\Client $guzzle )
	{
		$this->client = new $guzzle();
		$this->login();
	}

	public function getFeed()
	{
		$res = $this->client->get( 'https://laracasts.com/feed' );

		return $res->xml();
	}

	private function login()
	{
		$res = $this->client->post( 'https://laracasts.com/sessions', [
				'cookies' => TRUE,
				'body'    => [
					'email'    => getenv( 'laracasts.username' ),
					'password' => getenv( 'laracasts.password' ),
				],
			]);
	}

	public function getFileUrl( $url )
	{
		$res = $this->client->get( $url, [
				'cookies' => TRUE,
			]);

		preg_match('/<input name="lesson-id" type="hidden" value="(\d+)">/', $res, $matches );

		$id = last( $matches );

		$res = $this->client->get( 'https://laracasts.com/downloads/' . $id . '?type=lesson', [
				'cookies' => TRUE,
			]);

		 return $res->getEffectiveUrl();
	}

}