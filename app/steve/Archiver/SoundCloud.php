<?php

namespace Steve\Archiver;

use Carbon\Carbon;

class SoundCloud extends Archiver {

    public function archive()
    {
        $client = new \GuzzleHttp\Client;
        foreach ($this->result->list as $result) {

            try {
                $res = $client->get($result->resolved_url);
            } catch (\Exception $e) {
                continue;
            }

            $pattern = '/https:\/\/w\.soundcloud\.com\/player\/\?url=https%3A%2F%2Fapi.soundcloud.com%2Ftracks%2F(\d+)/';
            preg_match($pattern, (string) $res->getBody(), $matches);

            if (!empty($matches)) {
                $res = $client->get("https://api.soundcloud.com/tracks", ['query' => [
                        'ids'         => $matches[1],
                        'client_id'   => 'b45b1aa10f1ac2941910a7f0d10f8e28',
                        'app_version' => 'acf910a',
                    ]]);

                $info = $res->json(['object' => true])[0];

                $res = $client->get("https://api.soundcloud.com/tracks/{$matches[1]}/streams", ['query' => [
                        'client_id'   => 'b45b1aa10f1ac2941910a7f0d10f8e28',
                        'app_version' => 'acf910a',
                    ]]);

                $files = $res->json(['object' => true]);

                if (!empty($files->http_mp3_128_url)) {

                    $record = \OfflinerVideo::firstOrNew([
                            'video_source' => 'soundcloud',
                            'video_id'     => $matches[1],
                        ]);

                    if ($record->id) {
                        continue;
                    }

                    \Log::info('Logging soundcloud <comment>' . $matches[1] . '</comment>...');
                    \Log::info('New pocket since: ' . $this->result->since);

                    $record->fill([
                            'pocket_id'    => $result->item_id,
                            'video_url'    => $files->http_mp3_128_url,
                            'video_title'  => $info->title,
                            'pocket_since' => Carbon::createFromTimeStamp($this->result->since),
                            'user_id'      => $this->user->id,
                        ]);

                    $record->save();
                }
            }
        }
    }

}
