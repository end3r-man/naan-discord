<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Str;
use App\Models\UserYoutube;
use Google\Client;
use Google\Service\YouTube;
use Laracord\Services\Service;

class CheckYoutube extends Service
{
    /**
     * The service interval.
     */
    protected int $interval = 15;

    /**
     * Handle the service.
     */
    public function handle(): void
    {
        $youtube = UserYoutube::all();

        foreach ($youtube as $value) {

            $setting = UserSetting::where('for', 'Youtube')->where('guild_id', $value->guild_id)->first();

            if (!is_null($setting) && $setting->state) {

                $res = $this->GetNewVideo($value);

                $this->HandleMessage($res, $value);
            }
        }
    }

    /**
     * HandleMessage
     *
     * @param  object $res
     * @param  object $value
     */
    private function HandleMessage($res, $value): void
    {
        $nid = $res[0]->contentDetails->videoId;

        if ($value->last != $nid) {

            $type = $this->GetVideoType($nid);

            $value->update([
                'last' => $nid
            ]);

            $this
                ->message("{$value->name} {$type}!")
                ->authorName($value->name)
                ->authorIcon($value->profile)
                ->title("{$res[0]->snippet->title}")
                ->url("https://www.youtube.com/watch?v={$nid}")
                ->field('Description', Str::words($res[0]->snippet->description, 15, '...'))
                ->imageUrl($res[0]->snippet->thumbnails->maxres->url ?? null)
                ->body("@everyone" . $value->name . " Posted New Video")
                ->color('#e5392a')
                ->footerText('Sent by நான்')
                ->send($value->channel->channel_id);
        }
    }

    protected function GetNewVideo($value)
    {
        $apiKey = env("YOUTUBE_API_" . rand(1, 2));

        $client = new Client();
        $client->setDeveloperKey($apiKey);
        $service = new YouTube($client);

        try {
            $res = $service->playlistItems->listPlaylistItems(
                ['part' => 'snippet', 'contentDetails'],
                ['playlistId' => $value->youtube_id]
            );
        } catch (\Throwable $th) {
            $this->console()->log($th);
        }

        return $res;
    }

    protected function GetVideoType($nid)
    {
        $apiKey = env("YOUTUBE_API_" . rand(1, 2));

        $client = new Client();
        $client->setDeveloperKey($apiKey);
        $service = new YouTube($client);

        try {
            $re = $service->videos->listVideos(
                ['part' => 'snippet'],
                ['id' => $nid]
            );
        } catch (\Throwable $th) {
            $this->console()->log($th);
        }


        return ($re->items[0]->snippet->liveBroadcastContent == 'live') ? 'is streaming now' : 'published a new video';
    }
}
