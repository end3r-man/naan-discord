<?php

namespace App\Commands;

use Discord\Builders\Components\Button;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class PingCommand extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'ping';

    /**
     * The command description.
     *
     * @var string|null
     */
    protected $description = 'Ping? Pong!';

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
            return $this
            ->message('Yo? Admin!')
            ->title('Wanna ban you')
            ->button('See The Docs', route: 'resources', emoji: '💻', style: Button::STYLE_SECONDARY)
            ->reply($message);
        } else {
            # code...
        }
    }

    /**
     * The command interaction routes.
     */
    public function interactions(): array
    {
        return [
            'resources' => fn (Interaction $interaction) => $this
                ->message('Check out the resources below to learn more about Laracord.')
                ->title('Laracord Resources')
                ->buttons([
                    'Documentation' => 'https://laracord.com',
                    'GitHub' => 'https://github.com/laracord/laracord',
                ])
                ->reply($interaction, ephemeral: true),
        ];
    }
}
