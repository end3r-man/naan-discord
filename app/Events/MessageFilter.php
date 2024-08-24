<?php

namespace App\Events;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserSetting;
use App\Models\UserWarning;
use Carbon\Carbon;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event as Events;
use Laracord\Events\Event;

use function Discord\contains;

class MessageFilter extends Event
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
        if (!is_null($message->guild)) {

            $admin = false;

            foreach ($message->guild->members as $member) {
                if ($member->user->id == $message->author->id) {
                    foreach ($member->roles as $role) {
                        if ($role->permissions->administrator) {
                            $admin = true;
                            break 2;
                        }
                    }
                }
            }

            if (!$admin) {

                $channel = UserChannel::where('guild_id', $message->guild_id)
                    ->where('for', 'Link')
                    ->get();

                $log = UserSetting::where('for', 'Link')->first();

                $containslink = false;

                if (!is_null($log) && $log->state == true) {

                    $chid = null;

                    foreach ($channel as $value) {

                        if (!is_null($value->data)) {
                            $domain = json_decode($value->data);

                            foreach ($domain as $word) {
                                if (stripos($message->content, $word) !== false) {
                                    $containslink = true;
                                    $chid = $value->channel_id;
                                    break;
                                }
                            }
                        }
                        
                    }

                    if ($containslink == true && $message->channel_id != $chid) {

                        $chn = $message->guild->channels->get('id', $chid);

                        $this
                            ->message("Avoid Sending Promotion Link Outside, **{$message->author}**!")
                            ->title("❌ Use Self-Promo")
                            ->color('#213555')
                            ->field('Send Here', $chn->__toString())
                            ->footerText('Sent by நான்')
                            ->reply($message);

                        $message->delete();
                    }
                }
            }
        }
    }
}
