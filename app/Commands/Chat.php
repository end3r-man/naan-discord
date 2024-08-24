<?php

namespace App\Commands;

use Discord\Parts\Interactions\Interaction;
use Laracord\Commands\Command;
use OpenAI;
use Illuminate\Support\Str;

class Chat extends Command
{
    /**
     * The command name.
     *
     * @var string
     */
    protected $name = 'chat';

    /**
     * The command description.
     *
     * @var string|null
     */
    protected $description = 'Chat with the bot';

    /**
     * The command usage.
     *
     * @var string
     */
    protected $usage = '<message>';

    /**
     * The OpenAI API key.
     *
     * @var string
     */
    protected $apiKey = '';

    /**
     * The OpenAI client instance.
     *
     * @var \OpenAI\Client
     */
    protected $client;

    /**
     * The OpenAI API key.
     */
    // protected string $apiKey = '';

    /**
     * The OpenAI system prompt.
     */
    protected string $prompt = 'You only reply with 1-2 sentences at a time as if responding to a chat message.';

    /**
     * Handle the command.
     *
     * @param  \Discord\Parts\Channel\Message  $message
     * @param  array  $args
     * @return mixed
     */
    public function handle($message, $args)
    {
        $input = trim(
            implode(' ', $args ?? [])
        );

        if (! $input) {
            return $this
                ->message('You must provide a message.')
                ->title('Chat')
                ->color('#213555')
                ->error()
                ->send($message);
        }

        $message->channel->broadcastTyping()->done(function () use ($message, $input) {
            $key = "{$message->channel->id}.chat.responses";
            $input = Str::limit($input, 384);

            $messages = cache()->get($key, [['role' => 'system', 'content' => $this->prompt]]);
            $messages[] = ['role' => 'user', 'content' => $input];

            $result = $this->client()->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => $messages,
            ]);

            $response = $result->choices[0]->message->content;

            $messages[] = ['role' => 'assistant', 'content' => $response];

            cache()->put($key, $messages, now()->addMinute());

            $us = $message->user_id;

            return $this
                ->message($response)
                ->color('#213555')
                ->field('To', $message->author->__toString())
                ->field('Token', $result->usage->totalTokens)
                ->footerText('Sent by நான்')
                ->send($message);
        });
    }

    /**
     * Retrieve the OpenAPI client instance.
     *
     * @return \OpenAI\Client
     */
    protected function client()
    {
        if ($this->client) {
            return $this->client;
        }

        return $this->client = OpenAI::client($this->apiKey());
    }

    /**
     * Retrieve the OpenAPI API key.
     *
     * @return string
     */
    protected function apiKey()
    {
        if ($this->apiKey) {
            return $this->apiKey;
        }

        return $this->apiKey = env('OPENAI_API_KEY', $this->apiKey);
    }
}