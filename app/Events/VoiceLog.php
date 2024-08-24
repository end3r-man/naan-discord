<?php

namespace App\Events;

use App\Models\UserChannel;
use App\Models\UserSetting;
use App\Models\UserStats;
use App\Models\UserSystem;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\WebSockets\VoiceStateUpdate as WebSocketsVoiceStateUpdate;
use Discord\WebSockets\Event as Events;
use Discord\WebSockets\Events\VoiceStateUpdate;
use Laracord\Events\Event;

class VoiceLog extends Event
{
    /**
     * The event handler.
     *
     * @var string
     */
    protected $handler = Events::VOICE_STATE_UPDATE;

    /**
     * Handle the event.
     */
    public function handle(WebSocketsVoiceStateUpdate $state, Discord $discord, $oldstate)
    {
        $log = UserSetting::where('for', 'Voice')->first();

        if (!is_null($log) && $log->state) {
            if (is_null($state->channel_id)) {

                $channel = UserChannel::where('guild_id', $state->guild_id)
                    ->where('for', 'Voice')
                    ->first();

                if (!is_null($channel)) {
                    $sen = $discord->guilds->get('id', $channel->guild_id)->channels->get('id', $channel->channel_id);

                    $user = $discord->users->get('id', $oldstate->user_id);

                    $this
                        ->message("{$user->username} left {$oldstate->channel->name}")
                        ->authorName($user->username)
                        ->authorIcon($user->avatar)
                        ->color('#e5392a')
                        ->title("📢 Member left a channel")
                        ->field('ID', $oldstate->user_id)
                        ->footerText('Sent by நான்')
                        ->send($sen);
                } else {
                    $this->ErrorHandler($oldstate, $discord);
                }
            } elseif (is_null($oldstate)) {

                $channel = UserChannel::where('guild_id', $state->guild_id)
                    ->where('for', 'Voice')
                    ->first();

                if (!is_null($channel)) {
                    $sen = $discord->guilds->get('id', $channel->guild_id)->channels->get('id', $channel->channel_id);

                    $user = $discord->users->get('id', $state->user_id);

                    $this->HandleExp($state);

                    $this
                        ->message("{$user->username} joined {$state->channel->name}")
                        ->authorName($user->username)
                        ->authorIcon($user->avatar)
                        ->color('#213555')
                        ->title("📢 Member joined a channel")
                        ->field('ID', $state->user_id)
                        ->footerText('Sent by நான்')
                        ->send($sen);
                } else {
                    $this->ErrorHandler($state, $discord);
                }
            }
        }
    }

    public function ErrorHandler($om, $discord)
    {
        $ad = $discord->guilds->get('id', $om->guild_id);

        $adm = $ad->owner_id;

        $this
            ->message("Setup Voice Log Channel")
            ->title("❌ Channel Not Found")
            ->footerText('Sent by நான்')
            ->sendTo($adm);
    }

    protected function HandleExp($state)
    {
        $rpg = UserSetting::where('for', 'Rpg')->where('guild_id', $state->guild_id)->first();

        if (!is_null($rpg) && $rpg->state) {
            $user = UserStats::where('user_id', $state->user_id)
                ->where('guild_id', $state->guild_id)
                ->first();

            if (!$state->user->bot) {
                if (is_null($user)) {
                    UserStats::create([
                        'user_id' => $state->user_id,
                        'guild_id' => $state->guild_id,
                        'role_id' => $state->member->roles->first()->id,
                        'point' => 10
                    ]);
                } else {

                    $set = UserSetting::where('guild_id', $state->guild_id)->where('for', 'Rpg')->first();

                    if (!is_null($set) && $set->state) {
                        $nex = UserSystem::where('guild_id', $state->guild_id)->where('min_point', '>', $user->point)->orderBy('min_point', 'asc')->first();

                        $user->update([
                            'point' => $user->point + 10
                        ]);

                        if ( !is_null($nex) && !is_null($nex->min_point) && $user->point == $nex->min_point) {

                            $guild = $state->guild;

                            $role = $state->guild->roles->get('id', $nex->role_id);

                            $guild->members->get('id', $state->user_id)->setRoles([$role], 'User Role Updated');

                            return $this
                                ->message("On achieving **{$role}** role in **{$state->guild->name}**!")
                                ->title("🎉 Congratulations {$state->user->username}")
                                ->footerText('Sent by நான்')
                                ->send($state->channel_id);
                        }
                    }
                }
            }
        }
    }
}
