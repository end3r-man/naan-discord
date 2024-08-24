<?php

namespace App\Events;

use App\Models\UserChannel;
use App\Models\UserSetting;
use App\Models\UserStats;
use App\Models\UserSystem;
use Discord\Discord;
use Discord\Helpers\Collection;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

class MessageLog extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::MESSAGE_CREATE;

    /**
     * Handle the event.
     */
    public function handle(Message $message, Discord $discord)
    {
        // $this->console()->log($message->guild->owner_id);

        $log = UserSetting::where('for', 'Message')->where('guild_id', $message->guild_id)->first();
        $rpg = UserSetting::where('for', 'Rpg')->where('guild_id', $message->guild_id)->first();

        if (!is_null($log) && $log->state) {

            $channel = UserChannel::where('guild_id', $message->guild_id)
                ->where('for', 'Message')
                ->first();

            if (!is_null($channel)) {

                if ($message->author->bot == false) {
                    $this
                        ->message("Message Is: {$message->content}")
                        ->authorName($message->author->username)
                        ->authorIcon($message->author->avatar)
                        ->color('#e5392a')
                        ->title("📢 Member Messaged in {$message->channel->name}")
                        ->footerText('Sent by நான்')
                        ->send($channel->channel_id);
                }
            } else {

                $ad = $discord->guilds->get('id', $message->guild_id);

                $adm = $ad->owner_id ?? null;

                if (!is_null($adm)) {
                    $this
                        ->message("Setup Message Log Channel")
                        ->title("❌ Channel Not Found")
                        ->footerText('Sent by நான்')
                        ->sendTo($adm);
                }
            }
        }

        if (!is_null($rpg) && $rpg->state && !$message->author->bot) {
            $user = UserStats::where('user_id', $message->user_id)
                ->where('guild_id', $message->guild_id)
                ->first();

            if (is_null($user)) {
                UserStats::create([
                    'user_id' => $message->user_id,
                    'guild_id' => $message->guild_id,
                    'role_id' => $message->member->roles->first()->id,
                    'point' => 5
                ]);
                
            } else {

                $set = UserSetting::where('guild_id', $message->guild_id)->where('for', 'Rpg')->first();

                if (!is_null($set) && $set->state) {

                    $nex = UserSystem::where('guild_id', $message->guild_id)->where('min_point', '>', $user->point)->orderBy('min_point', 'asc')->first();

                    $user->update([
                        'point' => $user->point + 5
                    ]);

                    if (!is_null($nex) && !is_null($nex->min_point) && $user->point == $nex->min_point) {

                        $guild = $message->guild;

                        $role = $message->guild->roles->get('id', $nex->role_id);

                        $guild->members->get('id', $message->user_id)->setRoles([$role], 'User Role Updated');

                        return $this
                            ->message("On achieving **{$role}** role in **{$message->guild->name}**!")
                            ->title("🎉 Congratulations {$message->author->username}")
                            ->footerText('Sent by நான்')
                            ->reply($message);
                    }
                }
            }
        }
    }
}
