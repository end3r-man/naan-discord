<?php

namespace App\Commands;

use App\Models\UserGuild;
use App\Models\UserWarning;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class Init extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'init';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Setup Server.';

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

            $ad = $this->discord->guilds->get('id', $message->guild_id);

            $adm = $message->guild ?? null;

            $gul = UserGuild::where('guild_id', $message->guild_id)->first();

            if (is_null($gul) && !is_null($ad->owner_id)) {
                UserGuild::create([
                    'owner_id' => "$adm->owner_id",
                    'guild_id' => $message->guild_id,
                    'guild_name' => $message->guild->name
                ]);

                $this
                    ->message()
                    ->title("✅ Setup Completed")
                    ->color('#213555')
                    ->content("Server setup completed")
                    ->reply($message);

            } elseif (!is_null($gul) && !is_null($ad->owner_id)) {
                
                $gul->update([
                    'owner_id' => "$ad->owner_id",
                    'guild_id' => $message->guild_id,
                    'guild_name' => $message->guild->name
                ]);

                $this
                    ->message()
                    ->title("✅ Setup Updated")
                    ->color('#213555')
                    ->content("Server Updated To Bot")
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
            'wave' => fn (Interaction $interaction) => $this->message('👋')->reply($interaction),
        ];
    }
}
