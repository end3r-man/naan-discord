<?php

namespace App\Events;

use App\Models\UserChannel;
use Discord\Discord;
use Discord\Parts\User\Member;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

class Leave extends Event
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

        $this
            ->message("GoodBye **{$member->user}**, 👋 {$member->guild->name}")
            ->title("👋 See ya, {$member->username}")
            ->footerText('Sent by நான்')
            ->sendTo($member->user);


        $channel = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Allmember')
            ->first();

        $botdb = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Bot')
            ->first();

        $memb = UserChannel::where('guild_id', $member->guild_id)
            ->where('for', 'Member')
            ->first();

        if (!is_null($channel)) {

            $guild = $discord->guilds->get('id', $channel->guild_id);

            $chan = $discord->guilds->get('id', $channel->guild_id)->channels->get('id', $channel->channel_id);

            $name = "All Member: {$guild->member_count}";

            $chan->name = $name;

            $guild->channels->save($chan, 'All Member Channel Updated');

            echo $discord->guilds->get('id', $channel->guild_id)->channels->get('id', $channel->channel_id);
        }

        if (!is_null($botdb)) {

            $guild = $discord->guilds->get('id', $botdb->guild_id);

            $bchan = $discord->guilds->get('id', $botdb->guild_id)->channels->get('id', $botdb->channel_id);

            $bot = 0;

            foreach ($guild->members as $member) {
                if ($member->user->bot == true) {
                    $bot = $bot + 1;
                }
            }

            $name = "Bot: $bot";

            $bchan->name = $name;

            $guild->channels->save($bchan, "Bot Channel Updated");

            echo $discord->guilds->get('id', $botdb->guild_id)->channels->get('id', $botdb->channel_id);
        }

        if (!is_null($memb)) {

            $guild = $discord->guilds->get('id', $memb->guild_id);

            $mchan = $discord->guilds->get('id', $memb->guild_id)->channels->get('id', $memb->channel_id);

            $bot = 0;

            foreach ($guild->members as $member) {
                if ($member->user->bot == true) {
                    $bot = $bot + 1;
                }
            }

            $mes = $guild->member_count - $bot;

            $name = "Members: {$mes}";

            $mchan->name = $name;

            $guild->channels->save($mchan, "Member Channel Updated");

            echo $discord->guilds->get('id', $memb->guild_id)->channels->get('id', $memb->channel_id);
        }
    }
}
