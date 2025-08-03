<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TelegramServices;

class ProcessTelegramWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $telegramService;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function handle(TelegramServices $telegramService): void
    {
        $this->telegramService = $telegramService;

        if (!isset($this->request['message']) && !isset($this->request['callback_query'])) {
            return;
        }

        if (isset($this->request['message']['text']) && $this->request['message']['text'] === '/start') {
            $chatId = $this->request['message']['chat']['id'];
            $firstName = $this->request['message']['from']['first_name'];
            $username = $this->request['message']['from']['username'] ?? null;

            $this->telegramService->handleStartCommand($chatId, $firstName, $username);
        }

        if (isset($this->request['callback_query'])) {
            $chatId = $this->request['callback_query']['message']['chat']['id'];
            $callbackData = $this->request['callback_query']['data'];

            $this->telegramService->handleCallbackQuery($callbackData, $chatId);
        }

        if (isset($this->request['message']['text']) && $this->request['message']['text'] !== '/start') {
            $chatId = $this->request['message']['chat']['id'];
            $message = $this->request['message']['text'];
            $firstName = $this->request['message']['from']['first_name'] ?? 'Amigo';

            $this->telegramService->handleMessage($chatId, $message, $firstName);
        }
    }
}
