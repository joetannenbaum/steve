<?php

namespace Steve\Downloader;

use Carbon\Carbon;
use GuzzleHttp\Client;

class SoundCloud {

    protected $tag_writer;

    protected $info;

    protected $client;

    protected $track;

    protected $artist;

    public function __construct()
    {
        $this->initTagWriter();

        $this->client = new Client([
                                    'base_url' => 'http://api.soundcloud.com',
                                    'defaults' => [
                                                    'query' => [
                                                        'client_id' => getenv('soundcloud.client_id'),
                                                    ],
                                                ],
                                    ]);
    }

    protected function initTagWriter()
    {
        require_once app_path('../lib/getid3/getid3.php');
        require_once app_path('../lib/getid3/write.php');

        $this->tag_writer                    = new \getid3_writetags;
        $this->tag_writer->tagformats        = ['id3v2.3'];
        $this->tag_writer->overwrite_tags    = true;
        $this->tag_writer->tag_encoding      = 'UTF-8';
        $this->tag_writer->remove_other_tags = true;
    }

    public function download($url)
    {
        $this->getInfo($url);

        $audio_path = $this->downloadAudioFile();
        $art_path   = $this->downloadArtFile();

        $this->tag_writer->filename = $audio_path;

        $tag_data = [
            'title'  => [$this->info->title],
            'artist' => [$this->artist->full_name],
            'year'   => [(new Carbon($this->track->created_at))->format('Y')],
            'genre'  => [$this->track->genre],
        ];

        if ($fd = @fopen($art_path, 'rb')) {
            $tag_data['attached_picture'] = [
                [
                    'data'          => fread($fd, filesize($art_path)),
                    'picturetypeid' => 0x03, // 'Cover (front)'
                    'description'   => 'Cover',
                    'mime'          => 'image/jpeg',
                ]
            ];

            fclose($fd);
        }

        $this->tag_writer->tag_data = $tag_data;
        $this->tag_writer->WriteTags();

        return \Response::download($audio_path);
    }

    protected function downloadArtFile()
    {
        $art  = $this->getArtUrl();
        $path = storage_path(pathinfo($art, PATHINFO_BASENAME));

        file_put_contents($path, file_get_contents($art));

        return $path;
    }

    protected function downloadAudioFile()
    {
        $path = storage_path(\Str::slug($this->info->title) . '.' . $this->getAudioExtension());

        file_put_contents($path, file_get_contents($this->info->url));

        return $path;
    }

    protected function getInfo($url)
    {
        $this->info   = json_decode(exec("youtube-dl '" . $url . "' --print-json --simulate"));

        $track        = $this->client->get('/tracks/' . $this->info->display_id . '.json');
        $this->track  = $track->json(['object' => true]);

        $artist       = $this->client->get('/users/' . $this->track->user->id . '.json');
        $this->artist = $artist->json(['object' => true]);
    }

    protected function getAudioExtension()
    {
        return head(explode('?', pathinfo($this->info->url, PATHINFO_EXTENSION))) ?: 'mp3';
    }

    protected function getArtistArt()
    {
        return str_replace('-large.jpg', '-t500x500.jpg', $this->artist->avatar_url);
    }

    protected function getArtUrl()
    {
        return $this->info->thumbnail ?: $this->getArtistArt();
    }
}
