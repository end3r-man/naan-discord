<?php

namespace App\Commands;

use App\Models\UserStats;
use App\Models\UserSystem;
use App\Models\UserWarning;
use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;

class System extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'system';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'The RPG system.';

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
            $ty = $args[0];
            $pn = $args[1] ?? null;

            if ($ty != "help" && $ty != "list" && !is_null($ty) && is_numeric($pn)) {
                $this
                    ->message("Select a role")
                    ->title("📢 System " . ucfirst($ty))
                    ->color('#213555')
                    ->select(type: 'role', route: "select:{{$ty}, {$pn}}", placeholder: 'Select a role...')
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == "help") {
                $this
                    ->message("System Args and usage")
                    ->title("📢 RPG System Help")
                    ->color('#213555')
                    ->fields([
                        'Add' => 'To Add Exp',
                        'Update' => 'To Update Exist Exp',
                        'Remove' => 'To Remove Exp',
                        'List' => 'To List All Exp',
                    ])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == "list") {

                $sys = UserSystem::where('guild_id', $message->guild_id)->get();

                foreach ($sys as $value) {
                    $role = $message->guild->roles->get('id', $value->role_id);

                    $this
                        ->message("System Exps and Roles")
                        ->title("📢 RPG System List")
                        ->color('#213555')
                        ->field("Exp Req", "{$value->min_point}Exp")
                        ->field("For Role", "{$role}")
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
            'select:{type?, point?}' => fn (Interaction $interaction, ?string $type = null) => $this->handleSelect($interaction, $type),
        ];
    }


    /**
     * To handle the interaction routes.
     */
    protected function handleSelect(Interaction $interaction, ?string $type = 'Default'): void
    {
        $rpv = str_replace(['{', '}', ','], '', $type);

        $exp = explode(" ", $rpv);

        $act = $exp[0];

        $point = $exp[1];

        $role = $interaction->data->values[0];

        $system = UserSystem::where('guild_id', $interaction->guild_id)->where('min_point', $point)->first();

        if ($act == "add" && is_null($system)) {

            UserSystem::create([
                'role_id' => $role,
                'guild_id' => $interaction->guild_id,
                'min_point' => $point
            ]);

            $this
                ->message("System Added {$point}Exp")
                ->title("✅ Points Added")
                ->color('#213555')
                ->reply($interaction, ephemeral: true);
        } elseif ($act == "remove" && $system->min_point == $point) {
            $system->delete();

            $this
                ->message("System Deleted {$point}Exp")
                ->title("✅ Points Removed")
                ->color('#213555')
                ->reply($interaction, ephemeral: true);
        } elseif ($act == "update" && $system->min_point == $point) {
            $system->update([
                'role_id' => $role,
                'min_point' => $point
            ]);

            $this
                ->message("System Updated {$point}Exp")
                ->title("✅ Points Updated")
                ->color('#213555')
                ->reply($interaction, ephemeral: true);
        } elseif (!is_null($system)) {
            $this
                ->message("Already Found {$point}Exp")
                ->title("❌ Condition Found")
                ->color('#213555')
                ->reply($interaction, ephemeral: true);
        }
    }
}
