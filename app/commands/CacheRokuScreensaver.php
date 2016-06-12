<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CacheRokuScreensaver extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cache:roku-screensaver';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache photos from dropbox for Roku Screensaver';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info('Contacting Dropbox...');

        $previous_cache = \Cache::get('roku-screensaver') ?: [];

        $client = new \GuzzleHttp\Client(['base_url' => 'https://api.dropbox.com/1/']);

        $response = $client->get('metadata/auto/Photos%2FRoku%20Screensaver', [
                'headers' => [
                    'Authorization' => 'Bearer ' . getenv('dropbox.access_token'),
                ],
                'query' => [
                    'locale'     => '',
                    'list'       => true,
                    'file_limit' => 25000,
                ]
            ]);

        $files = $response->json();

        $photos = $files['contents'];

        $cache_photos = [];

        $this->info('Found ' . count($photos) . ' photos...');

        foreach ($photos as $p) {
            if ($cached = $this->getFromCache($previous_cache, $p['path'])) {
                $cache_photos[] = $cached;
                continue;
            }

            $response = $client->get('media/dropbox' . $p['path'], [
                    'headers' => [
                        'Authorization' => 'Bearer ' . getenv('dropbox.access_token'),
                    ],
                ]);

            $photo = $response->json();

            $this->info('Downloading ' . $photo['url'] . '...');

            $new_file_path = 'images/roku-screensaver/' . last(explode('/', $photo['url']));

            file_put_contents(public_path($new_file_path), file_get_contents($photo['url']));

            $photo_info = getimagesize(public_path($new_file_path));

            $cache_photos[] = [
                    'url'    => str_replace('http://', 'https://', asset($new_file_path, true)),
                    'width'  => $photo_info[ 0 ],
                    'height' => $photo_info[ 1 ],
                ];
        }

        \Cache::put('roku-screensaver', $cache_photos, \Carbon\Carbon::now()->addHours(12));

        $this->info('Cached.');
    }

    protected function getFromCache($cached, $path)
    {
        $filename = urlencode(pathinfo($path, PATHINFO_BASENAME));
        foreach ($cached as $photo) {
            if ($filename == pathinfo($photo['url'], PATHINFO_BASENAME)) {
                $this->info('Found in cache: ' . $photo['url']);
                return $photo;
            }
        }
    }
}
