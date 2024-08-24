<?php

namespace App\Commands;

use App\Models\UserStats;
use App\Models\UserSystem;
use Discord\Parts\Interactions\Interaction;
use Illuminate\Support\Str;
use Laracord\Commands\Command;

use function PHPSTORM_META\type;

class Status extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'status';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'To Get status';

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
        $user = UserStats::where('guild_id', $message->guild_id)->where('user_id', $message->user_id)->first();
        $sett = UserSystem::where('guild_id', $message->guild_id)->where('min_point', '>=', $user->point)->orderBy('min_point', 'asc')->first();

        if (!is_null($user)) {

            $sat = $this->Handelexp($sett->min_point, $user->point);
            $per = round(($user->point / $sett->min_point * 100), 2);
            $role = $message->guild->roles->get('id', $sett->role_id);

            $this
                ->message("Your Status on **{$message->guild->name}**")
                ->authorName($message->author->username)
                ->authorIcon($message->author->avatar)
                ->color('#e5392a')
                ->title("📜 **{$message->author->username}** Status")
                ->field("Progress {$per}%", $sat)
                ->field('Next Role', $role)
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


    /**
     * To create the progress bar.
     * @param string $total
     * @param string $current
     */
    protected function Handelexp($total, $current): string
    {
        $progress = $current / $total * 10;
        $empty = 10 - $progress;

        $filledSymbol = '◻️';
        $emptySymbol = '◼️';

        return Str::of($filledSymbol)
            ->repeat($progress)
            ->append(Str::of($emptySymbol)->repeat($empty));
    }
}
