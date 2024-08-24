<?php

namespace App\Commands;

use App\Models\UserChannel;
use App\Models\UserWarning;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class Filter extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'filter';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Try @filter help';

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

            if ($args[0] == 'help') {
                $this
                    ->message("Domain name to filter, for list separate with comman")
                    ->title("📢 Add Domain")
                    ->color('#213555')
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } else {

                $domain = [];

                foreach ($args as $key => $value) {
                    $value = str_replace(",", "", $value);

                    array_push($domain, $value);
                }

                $jsm = json_encode($domain);

                $channel = UserChannel::where('channel_id', $message->channel_id)
                    ->where('for', 'Link')
                    ->first();


                if (!is_null($channel)) {

                    $channel->update([
                        'data' => $jsm
                    ]);

                    $dm = json_encode($domain);

                    $this
                        ->message("{$dm} Added")
                        ->title("📢 Domain List Updated")
                        ->color('#213555')
                        ->footerText('Sent by நான்')
                        ->reply($message);
                } else {

                    $this
                        ->message("Add Channel To Filter List **@channel help**")
                        ->title("❌ Channel Not Found")
                        ->color('#213555')
                        ->footerText('Sent by நான்')
                        ->reply($message);
                }
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
