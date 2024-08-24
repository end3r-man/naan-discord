<?php

namespace App\Commands;

use App\Models\UserWarning;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class Unban extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'unban';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Unban member.';

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
            $user = $args[0] ?? null;

            $logLine = trim($user);
            $logLine = str_replace(['[', ']'], '', $logLine);
            $logParts = explode(' ', $logLine, 4);

            $use = str_replace(['<@', '>', '"', '<>'], '', $logParts[0]);

            // $message->guild->unban($mem)->then(fn () => $this->handleKickBehavior($message, $mem), fn ($e) => $this->handleYourError($e, $message));

        } else {

            $user = UserWarning::where('user_id', $message->user_id)->first();

            $wc = 0;

            if (is_null($user)) {
                UserWarning::create([
                    'user_id' => $message->user_id,
                    'guild_id' => $message->guild_id,
                    'uwarning' => 1
                ]);

                $wc = 0;
            } else {
                $user->update([
                    'uwarning' => $user->uwarning + 1,
                ]);

                $wc = $user->uwarning;
            }

            if ($wc <= 3)
            {
                return $this
                    ->message()
                    ->title('❌ Admin Only')
                    ->content('Warning +1 Added!')
                    ->color('#213555')
                    ->field('Count', $wc)
                    ->field('User', $message->author->__toString())
                    ->footerText('Sent by நான்')
                    ->send($message);

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

    public function handleKickBehavior($message)
    {
        return $this
            ->message()
            ->title('✅ User UnBanned')
            ->color('#213555')
            ->content("User Has Been UnBanned")
            ->footerText('Sent by நான்')
            ->send($message);
    }

    public function handleYourError($e, $message)
    {
        return $this
            ->message()
            ->title('❌ Permission Denied')
            ->color('#213555')
            ->content("Doesn't Have Permission")
            ->footerText('Sent by நான்')
            ->send($message);
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
