<?php

namespace App\Events;

use App\Models\UserChannel;
use App\Models\UserSetting;
use Discord\Discord;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

class LeaveLog extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::GUILD_MEMBER_REMOVE;

    /**
     * Handle the event.
     */
    public function handle(Member $member, Discord $discord)
    {
        $this->console()->log('The Guild Member Remove event has fired!');

        $channel = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Update')
            ->first();

        $log = UserSetting::where('for', 'Update')->first();

        if (!is_null($log) && $log->state) {
            if (!is_null($channel)) {

                if ($member->user->bot == false) {
                    $this
                        ->message('')
                        ->authorName($member->user->username)
                        ->authorIcon($member->user->avatar)
                        ->field('Joined', $member->joined_at->format('d m Y'))
                        ->field('Role', $member->roles->first())
                        ->color('#e5392a')
                        ->title("📢 Member Left")
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
