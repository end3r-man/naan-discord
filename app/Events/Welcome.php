<?php

namespace App\Events;

use App\Models\UserChannel;
use App\Models\UserSetting;
use Discord\Discord;
use Laracord\Discord\Message;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event as Events;
use Illuminate\Support\Arr;
use Laracord\Events\Event;
use Ramsey\Uuid\Guid\Guid;

class Welcome extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::GUILD_MEMBER_ADD;

    /**
     * Handle the event.
     */
    public function handle(Member $member, Discord $discord)
    {

        $welcome = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Welcome')
            ->first();

        $log = UserSetting::where('for', 'Update')->first();

        if (!is_null($welcome)) {
            if (isset($welcome->data)) {
                $this
                ->message("Welcome to the {$member->guild->name}, **{$member->user}**!")
                ->title("👋 Welcome {$member->username}")
                ->imageUrl($welcome->data)
                ->footerText('Sent by நான்')
                ->send($welcome->channel_id);
    
            } else {
                $this
                ->message("Welcome to the {$member->guild->name}, **{$member->user}**!")
                ->title("👋 Welcome {$member->username}")
                ->footerText('Sent by நான்')
                ->send($welcome->channel_id);
            }
        }

        $channel = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Update')
            ->first();

        if (!is_null($log) && $log->state) {
            if (!is_null($channel)) {

                if ($member->user->bot == false) {
                    $this
                        ->message('Joined the server')
                        ->authorName($member->user->username)
                        ->authorIcon($member->user->avatar)
                        ->color('#e5392a')
                        ->title("📢 Member Joined")
                        ->footerText('Sent by நான்')
                        ->send($channel->channel_id);
                }
            } else {

                $ad = $discord->guilds->get('id', $member->guild_id);

                $adm = $ad->owner_id ?? null;

                if (!is_null($adm)) {
                    $this
                        ->message("Setup Join Leave Log Channel")
                        ->title("❌ Channel Not Found")
                        ->footerText('Sent by நான்')
                        ->sendTo($adm);
                }
            }
        }
    }
}
