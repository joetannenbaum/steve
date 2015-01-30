<?php

namespace Steve\External;

class Pocket {

	private $consumer_key;

	private $access_token;

	private $api_base = 'https://getpocket.com/v3/';

	public function __construct($access_token = null)
	{
		$this->consumer_key = getenv('pocket.consumer_key');

		if ($access_token) {
			$this->access_token = $access_token;
		}
	}

	public function search($params = [])
	{
		$client = $this->getClient();

		$res = $client->get('get', ['query' => $this->getParams($params)]);
		return $res->json(['object' => true]);
	}

	protected function getClient()
	{
		return new \GuzzleHttp\Client(['base_url' => $this->api_base]);
	}

	protected function getParams($params)
	{
		$default = [
				'consumer_key' => $this->consumer_key,
				'access_token' => $this->access_token,
				'detailType'   => 'complete',
			];

		return array_merge($default, $params);
	}

	public function getAuthUrl()
	{
		$client = $this->getClient();
		$res = $client->post('oauth/request', [
				'json' => [
					'consumer_key' => $this->consumer_key,
					'redirect_uri' => url('pocket-authorized'),
				]
			]);

		$response = (string) $res->getBody();
		parse_str($response);

		\Session::put('pocket_token', $code);

		$params   = [
			'request_token' => $code,
			'redirect_uri'  => url('pocket-authorized'),
		];

		return 'https://getpocket.com/auth/authorize?' . http_build_query($params);
	}

	public function finishAuth()
	{
		$client = $this->getClient();
		$res = $client->post('oauth/authorize', [
				'json' => [
					'consumer_key' => $this->consumer_key,
					'code'         => \Session::get('pocket_token'),
				]
			]);

		$response = (string) $res->getBody();

		parse_str($response);

		\Session::forget('pocket_token');

		return compact('username', 'access_token');
	}

}
