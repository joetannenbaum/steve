<?php

namespace Steve\External;

class Laracasts {

	private $client;

	public function __construct( \GuzzleHttp\Client $guzzle )
	{
		$this->client = new $guzzle();
		$this->login();
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

	public function getNewLessons()
	{
		$html    = file_get_contents( 'https://laracasts.com/latest');
		$crawler = new \Symfony\Component\DomCrawler\Crawler( $html, 'https://laracasts.com' );
		$list    = $crawler->filter('.list-group')->first()->filter('a')->links();

		$urls = [];

		foreach ( $list as $l )
		{
			$urls[] = $l->getUri();
		}

		return $urls;
	}

	public function getFileUrls( $url )
	{
		$res = $this->client->get( $url, [
				'cookies' => TRUE,
			]);

		$crawler = new \Symfony\Component\DomCrawler\Crawler( (string) $res->getBody(), 'https://laracasts.com' );

		$links = $crawler->filter('.lesson-meta')->first()->filter('a')->links();

		$title = $crawler->filter('title')->first()->text();

		$return_urls = [];

		foreach ( $links as $l )
		{
			$link_url = $l->getUri();

			if ( preg_match('/https:\/\/laracasts.com\/downloads\/(\d+)\?type=(episode|lesson)/', $link_url ) )
			{
				$return_urls[ $link_url ] = $title;
			}
		}

		return $return_urls;
	}

	public function getDownloadUrl( $url )
	{
		return $this->client->get( $url, [
					'cookies' => TRUE,
				])->getEffectiveUrl();
	}

}