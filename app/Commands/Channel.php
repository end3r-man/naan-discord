<?php

namespace App\Commands;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserWarning;
use Discord\Parts\Channel\Channel as ChannelChannel;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class Channel extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'channel';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Try @channel help';

    /**
     * Determines whether the command requires admin permissions.
     *
     * @var bool
     */
    protected $admin = false;

    /**
     * Determines whether the command should be displayed in the commands list.
     *
     * @var bool
     */
    protected $hidden = false;

    /**
     * The command usage.
     *
     * @var string
     */
    protected $usage = '<welcome>';

    public function handle($message, $args)
    {
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

        if ($admin) {

            $chn = $message->guild->channels;

            $ty = $args[0];
            $domain = $args[1] ?? null;

            if ($ty == 'welcome' || $ty == 'voice' || $ty == 'message' || $ty == 'update' || $ty == 'link') {
                $this
                    ->message("Select a channel for {$ty}")
                    ->title("📢 Setup Channel")
                    ->color('#213555')
                    ->select(type: 'channel', route: "select:{$ty}", placeholder: 'Select a channel...', options: ['channelTypes' => [ChannelChannel::TYPE_GUILD_TEXT]])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == 'allmember' || $ty == 'bot' || $ty == 'member') {
                $this
                    ->message("Select a channel for {$ty}")
                    ->title("📢 Setup Channel")
                    ->color('#213555')
                    ->select(type: 'channel', route: "select:{$ty}", placeholder: 'Select a channel...', options: ['channelTypes' => [ChannelChannel::TYPE_GUILD_VOICE]])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == "help") {
                $this
                    ->message("Channel Args and usage")
                    ->title("📢 Setup Channel Help")
                    ->color('#213555')
                    ->fields([
                        'Welcome' => 'To send welcome message',
                        'voice' => 'To send voice log',
                        'Message' => 'To send message log',
                        'Update' => 'Member Join Leave log',
                        'Allmember' => 'Update All-Member Count',
                        'Bot' => 'Update Bot Count',
                        'Member' => 'Update Member Count',
                        'Youtube' => 'To Send Youtube Video Notification',
                        'Link' => 'To Allow Self-pro Or Links'
                    ])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == 'youtube') {
                $this
                    ->message("Select a channel for {$ty}")
                    ->title("📢 Setup Channel")
                    ->color('#213555')
                    ->select(type: 'channel', route: "select:{$ty}", placeholder: 'Select a channel...', options: ['channelTypes' => [ChannelChannel::TYPE_GUILD_ANNOUNCEMENT]])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            }
        } else {

            $user = UserWarning::where('user_id', $message->user_id)->first();

            $wc = 0;

            if (is_null($user)) {
                UserWarning::create([
                    'user_id' => $message->user_id,
                    'guild_id' => $message->guild_id,
                    'uwarning' => 1
                ]);

                $wc = 1;
            } else {
                $user->update([
                    'uwarning' => $user->uwarning + 1,
                ]);

                $wc = $user->uwarning;
            }

            if ($wc <= 3) {
                return $this
                    ->message('Warning +1 Added!')
                    ->title('❌ Admin Only')
                    ->color('#213555')
                    ->field('Count', $wc)
                    ->field('User', $message->author->__toString())
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } else {

                $mem = $message->guild->members->get('id', $message->user_id);

                $mem->kick('Using Admin Command');

                return $this
                    ->message("For using Admin command many")
                    ->title("❌ User Banned")
                    ->color('#213555')
                    ->field('Count', $wc)
                    ->field('User', $message->author->__toString())
                    ->footerText('Sent by நான்')
                    ->send($message);
            }
        }
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction),
            'select:{type?}' => fn (Interaction $interaction, ?string $type = null) => $this->handleSelect($interaction, $type),
        ];
    }

        
    /**
     * handleSelect
     *
     * @param  mixed $interaction
     * @param  mixed $type
     * @return void
     */
    protected function handleSelect(Interaction $interaction, ?string $type = 'Default'): void
    {
        $type = ucfirst($type);

        $chan = UserChannel::where('guild_id', $interaction->guild_id)
            ->where('for', $type)
            ->first();

        $user = User::where('discord_id', $interaction->member->id)->first();

        if (is_null($user)) {

            $user = User::create([
                'username' => $interaction->member->username,
                'discord_id' => $interaction->member->id,
                'profile' => $interaction->member->user->avatar,
            ]);
        }

        if ($type == 'Link') {

            $chan = UserChannel::where('channel_id', $interaction->data->values[0])
                ->where('for', $type)
                ->first();

            if (!is_null($chan)) {
                $chan->update([
                    'channel_id' => $interaction->data->values[0],
                    'for' => $type
                ]);
            } else {
                UserChannel::create([
                    'user_id' => $user->id,
                    'guild_id' => $interaction->guild_id,
                    'channel_id' => $interaction->data->values[0],
                    'for' => $type
                ]);
            }
        }else {
            if (!is_null($chan)) {
                $chan->update([
                    'channel_id' => $interaction->data->values[0],
                    'for' => $type
                ]);
            } else {
                UserChannel::create([
                    'user_id' => $user->id,
                    'guild_id' => $interaction->guild_id,
                    'channel_id' => $interaction->data->values[0],
                    'for' => $type
                ]);
            }
        }

        if ($type != 'Link') {
            $this
                ->message()
                ->title("✅ {$type} Updated")
                ->color('#213555')
                ->content("{$type} Channel Updated")
                ->reply($interaction, ephemeral: true);
        } else {
            $this
                ->message()
                ->title("✅ {$type} Added to Filter")
                ->color('#213555')
                ->content("Add Domain To Filter Use **@filter help**")
                ->reply($interaction, ephemeral: true);
        }

        
    }
}
