<?php

namespace Steve\External;

class Pocket {

	private $consumer_key;

	private $access_token;

	private $api_base = 'https://getpocket.com/v3/';

	public function __construct()
	{
		$this->consumer_key = getenv('pocket.consumer_key');
		$this->access_token = getenv('pocket.access_token');
	}

	public function getVideos( $params = [] )
	{
		$default = [
				'contentType'  => 'video',
			];

		$params = array_merge( $default, $params );

		return $this->get( $params );
	}

	public function get( $params = [] )
	{
		//since
		$default = [
				'consumer_key' => $this->consumer_key,
				'access_token' => $this->access_token,
				'detailType'   => 'complete',
			];

		$params = array_merge( $default, $params );

		$client = new \GuzzleHttp\Client();

		$res = $client->get( $this->url('get'), [
			'query' => $params
		]);

		$res = $res->json([ 'object' => TRUE ]);

		return $res;
	}

	private function url( $append )
	{
		return $this->api_base . $append;
	}

}