<?php

namespace Steve\Archiver;

use Carbon\Carbon;

class Video extends Archiver {

    public function archive()
    {
        foreach ($this->videoResult() as $result) {
            foreach ($this->usableVideos($result->videos) as $video) {
                $record = \OfflinerVideo::firstOrNew([
                        'video_source' => $video->src_name,
                        'video_id'     => $video->vid,
                    ]);

                if ($record->id) {
                    continue;
                }

                \Log::info('Logging video <comment>' . $video->vid . '</comment>...');
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

    protected function videoResult()
    {
        foreach ($this->activeResult() as $result) {
            if (!empty($result->videos)) {
                yield $result;
            }
        }
    }

    protected function usableVideos($videos)
    {
        foreach ($this->validVideos($videos) as $video) {
            $video->src_name = $video->src;
            yield $video;
        }
    }

    protected function validVideos($videos)
    {
        foreach ($videos as $video) {
            yield $video;
        }
    }

}
