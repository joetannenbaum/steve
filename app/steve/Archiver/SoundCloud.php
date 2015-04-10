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
                $record = \OfflinerVideo::firstOrNew([
                        'video_source' => $result->resolved_url,
                        'video_id'     => $matches[1] . '|' . $result->resolved_url,
                    ]);

                if ($record->id) {
                    continue;
                }

                \Log::info('Logging soundcloud <comment>' . $matches[1] . '</comment>...');
                \Log::info('New pocket since: ' . $this->result->since);

                $record->fill([
                        'pocket_id'    => $result->item_id,
                        'pocket_since' => Carbon::createFromTimeStamp($this->result->since),
                        'user_id'      => $this->user->id,
                    ]);

                $record->save();
            }
        }
    }

}
