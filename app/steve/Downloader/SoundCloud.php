<?php

namespace Steve\Downloader;

use Carbon\Carbon;
use GuzzleHttp\Client;

class SoundCloud {

    protected $tag_writer;

    protected $tracks = [];

    protected $client;

    protected $track;

    protected $artist;

    protected $files = [];

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
        $this->getTracks($url);

        foreach ($this->tracks as $track) {
            $this->getTrackInfo($track);

            $audio_path = $this->downloadAudioFile($track);
            $art_path   = $this->downloadArtFile($track);

            $this->tag_writer->filename = $audio_path;

            $tag_data = [
                'title'  => [$track->title],
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

            $this->files[] = $audio_path;
        }

        if (count($this->files) === 1) {
            return \Response::download(head($this->files));
        }

        $zip = new \ZipArchive();
        $zip_filename = storage_path(head($this->tracks)->playlist_title . '.zip');

        if ($zip->open($zip_filename, \ZipArchive::CREATE) !== true) {
            throw new \Exception("Cannot open {$zip_filename}");
        }

        foreach ($this->files as $file) {
            $zip->addFile($file, pathinfo($file, PATHINFO_BASENAME));
        }

        $zip->close();

        return \Response::download($zip_filename);
    }

    protected function downloadArtFile($track)
    {
        $art  = $this->getArtUrl($track);
        $path = storage_path(pathinfo($art, PATHINFO_BASENAME));

        file_put_contents($path, file_get_contents($art));

        return $path;
    }

    protected function downloadAudioFile($track)
    {
        $path = storage_path(\Str::slug($track->title) . '.' . $this->getAudioExtension($track));

        file_put_contents($path, file_get_contents($track->url));

        return $path;
    }

    protected function getTracks($url)
    {
        exec("youtube-dl '" . $url . "' --print-json --simulate", $output);

        $this->tracks = array_map('json_decode', $output);
    }

    protected function getTrackInfo($current)
    {
        $track        = $this->client->get('/tracks/' . $current->display_id . '.json');
        $this->track  = $track->json(['object' => true]);

        $artist       = $this->client->get('/users/' . $this->track->user->id . '.json');
        $this->artist = $artist->json(['object' => true]);
    }

    protected function getAudioExtension($track)
    {
        return head(explode('?', pathinfo($track->url, PATHINFO_EXTENSION))) ?: 'mp3';
    }

    protected function getArtistArt()
    {
        return str_replace('-large.jpg', '-t500x500.jpg', $this->artist->avatar_url);
    }

    protected function getArtUrl($track)
    {
        return $track->thumbnail ?: $this->getArtistArt();
    }
}
