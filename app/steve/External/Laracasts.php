<?php

namespace Steve\External;

use GuzzleHttp\Client;

class Laracasts {

	private $client;

	public function __construct(Client $guzzle)
	{
		$this->client = new $guzzle();
		$this->login();
	}

	private function login()
	{
		$res = $this->client->post('https://laracasts.com/sessions', [
				'cookies' => true,
				'body'    => [
					'email'    => getenv('laracasts.username'),
					'password' => getenv('laracasts.password'),
				],
			]);
	}

	public function getNewLessons()
	{
		$res = $this->client->get('https://laracasts.com/feed');

		$urls = [];

		foreach ($res->xml() as $r) {
			if (!$r->link) {
				continue;
			}

			$urls[] = (string) $r->link->attributes()['href'];
		}

		return $urls;
	}

	public function getFileUrls($url)
	{
		$res = $this->client->get($url, [
				'cookies' => true,
			]);

		$crawler = new \Symfony\Component\DomCrawler\Crawler((string) $res->getBody(), 'https://laracasts.com');

		$links = $crawler->filter('.lesson-meta')->first()->filter('a')->links();

		$title = $crawler->filter('title')->first()->text();

		$return_urls = [];

		foreach ($links as $l) {
			$link_url = $l->getUri();

			if (preg_match('/https:\/\/laracasts.com\/downloads\/(\d+)\?type=(episode|lesson)/', $link_url)) {
				$return_urls[$link_url] = $title;
			}
		}

		return $return_urls;
	}

	public function getDownloadUrl($url)
	{
		return $this->client->get($url, [
					'cookies' => true,
				])->getEffectiveUrl();
	}

}
