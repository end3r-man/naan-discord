<?php

namespace App\Commands;

use App\Models\UserChannel;
use App\Models\UserWarning;
use App\Models\UserYoutube;
use Google\Client;
use Google\Service\YouTube as ServiceYouTube;
use Laracord\Commands\Command;

use function Laravel\Prompts\search;

class Youtube extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'youtube';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'The youtube command.';

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

            if ($ty == "help") {
                $this
                    ->message("Youtube Args and usage")
                    ->title("📢 Setup Youtube Notification")
                    ->color('#213555')
                    ->fields([
                        'Add' => 'To Add A Channel',
                        'Remove' => 'To Remove A Channel',
                    ])
                    ->footerText('Sent by நான்')
                    ->reply($message);
            } elseif ($ty == "add" && !is_null($args[1])) {


                $channel = UserChannel::where('guild_id', $message->guild_id)->where('for', 'Youtube')->first();

                if (!is_null($channel)) {

                    $res = $this->searchyt($args[1]);

                    $name = $res[0];

                    $plid = $res[1];

                    $profile = $res[2];

                    $ytb = UserYoutube::where('guild_id', $message->guild_id)->where('youtube_id', $plid)->first();

                    if (!is_null($ytb)) {
                        $this
                            ->message("**{$name}**, Channel Already Found!")
                            ->title("😅 Channel Found!")
                            ->footerText('Sent by நான்')
                            ->reply($message);
                    } else {
                        UserYoutube::create([
                            'channel_id' => $channel->id,
                            'guild_id' => $channel->guild_id,
                            'youtube_id' => $plid,
                            'name' => $name,
                            'profile' => $profile,
                            'last' => '0'
                        ]);

                        $this
                            ->message("**{$name}**, Channel Successfully Added!")
                            ->title("🥳 Channel Added")
                            ->footerText('Sent by நான்')
                            ->reply($message);
                    }
                } else {
                    $this
                        ->message("Setup A Notification Channel")
                        ->title("❌ Channel Not Found")
                        ->color('#213555')
                        ->footerText('Sent by நான்')
                        ->send($message);
                }
            } elseif ($ty == "remove" && !is_null($args[1])) {

                $res = $this->searchyt($args[1]);

                $name = $res[0];

                $plid = $res[1];

                $ytb = UserYoutube::where('guild_id', $message->guild_id)->where('youtube_id', $plid)->first();

                if (!is_null($ytb)) {

                    $this
                        ->message("**{$name}**, Channel No Longer Available!")
                        ->title("🗑️ Channel Removed")
                        ->footerText('Sent by நான்')
                        ->reply($message);

                    $ytb->delete();
                } else {
                    $this
                        ->message("Oops! Channel Not Found Here!")
                        ->title("😅 Channel Not Found")
                        ->footerText('Sent by நான்')
                        ->reply($message);
                }
            } elseif (is_null($args[1])) {
                $this
                    ->message("Please Provide Channel ID to Add a Channel!")
                    ->title("😅 Channel ID Not Found")
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

                $wc = 0;
            } else {
                $user->update([
                    'uwarning' => $user->uwarning + 1,
                ]);

                $wc = $user->uwarning;
            }

            if ($wc <= 3) {
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



    protected function searchyt($id)
    {
        $apiKey = env('YOUTUBE_API');

        $client = new Client();
        $client->setDeveloperKey($apiKey);
        $service = new ServiceYouTube($client);

        $res = $service->channels->listChannels(
            ['part' => 'snippet', 'contentDetails'],
            ['id' => $id]
        );

        $name = $res[0]->snippet->title;

        $plid = $res[0]->contentDetails->relatedPlaylists->uploads;

        $profile = $res[0]->snippet->thumbnails->high->url;

        return [$name, $plid, $profile];
    }
}
