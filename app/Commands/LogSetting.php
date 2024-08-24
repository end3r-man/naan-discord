<?php

namespace App\Commands;

use App\Models\User;
use App\Models\UserChannel;
use App\Models\UserSetting;
use App\Models\UserWarning;
use Discord\Parts\Channel\Channel as ChannelChannel;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class LogSetting extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'setting';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Try @setting help';

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
     * Handle the command.
     *
     * @param  \Discord\Parts\Channel\Message  $message
     * @param  array  $args
     * @return void
     */

    protected array $scha = [
        'on' => [
            'label' => 'On',
            'description' => 'To turn on',
            'emoji' => '✅',
        ],
        'off' => [
            'label' => 'Off',
            'description' => 'To turn off',
            'emoji' => '❌',
        ]
    ];

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

            $ty = $args[0];

            if ($ty == "update" || $ty == "message" || $ty == "voice" || $ty == "link" || $ty == "rpg" || $ty == "youtube") {
                $this
                    ->message("Turn on/off the {$ty} log")
                    ->title("📢 Setup Log Channel")
                    ->select($this->scha, route: "select:{$ty}", placeholder: 'Turn On Or Off...')
                    ->color('#213555')
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == "help") {
                $this
                    ->message("Turn On/Off Logs")
                    ->title("📢 Setup log message")
                    ->color('#213555')
                    ->fields([
                        'voice' => 'Member Join/Leave log',
                        'Message' => 'Member message log',
                        'Update' => 'Member Join Leave log',
                        'Link' => 'Member Link Filter',
                        'Rpg' => 'To On/Off RPG System',
                        'Youtube' => 'To On/Off Youtube notification',
                    ])
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
                    ->reply($message);
            }
        }
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            // 'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction), 
            'select:{type?}' => fn (Interaction $interaction, ?string $type = null) => $this->handleSelect($interaction, $type),
        ];
    }


    protected function handleSelect(Interaction $interaction, ?string $type = 'Default'): void
    {
        $type = ucfirst($type);

        $user = User::where('discord_id', $interaction->member->id)->first();

        $setting = UserSetting::where('for', $type)->where('guild_id', $interaction->guild_id)->first();

        $stat = null;

        if ($interaction->data->values[0] == 'on') {
            $stat = true;
        } elseif ($interaction->data->values[0] == 'off') {
            $stat = false;
        }

        if (is_null($user)) {

            $user = User::create([
                'username' => $interaction->member->username,
                'discord_id' => $interaction->member->id,
                'profile' => $interaction->member->user->avatar,
            ]);
        }

        if (!is_null($setting)) {
            $setting->update([
                'state' => $stat
            ]);
        } else {
            UserSetting::create([
                'user_id' => $user->id,
                'guild_id' => $interaction->guild_id,
                'for' => $type,
                'state' => $stat
            ]);
        }

        $this
            ->message("{$type} Settings has been updated")
            ->title("✅ {$type} Updated")
            ->color('#213555')
            ->content("{$type} Log Updated")
            ->reply($interaction, ephemeral: true);
    }
}
