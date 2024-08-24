<?php

namespace App\Commands;

use App\Models\UserChannel;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class WelcomeGif extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'welcome';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Add welcome-gif.';

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
    protected $hidden = true;

    /**
     * Handle the command.
     *
     * @param  \Discord\Parts\Channel\Message  $message
     * @param  array  $args
     * @return void
     */
    public function handle($message, $args)
    {
        $channel = UserChannel::where('guild_id', $message->guild_id)->where('for', 'Welcome')->first();

        if (!is_null($channel)) {

            $channel->update([
                'data' => $args[0]
            ]);

            $this
                ->message()
                ->title("✅ GIF Updated")
                ->color('#213555')
                ->content("Welcome GIF Updated")
                ->reply($message);
        } else {
            $this
                ->message("Setup Welcome Channel **@channel help**")
                ->title("❌ Channel Not Found")
                ->footerText('Sent by நான்')
                ->reply($message);
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
