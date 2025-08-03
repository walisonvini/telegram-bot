<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\TelegramUser;

class TelegramServices
{
    private $telegramApiUrl;
    private $telegramBotToken;

    public function __construct()
    {
        $this->telegramApiUrl = "https://api.telegram.org/bot";
        $this->telegramBotToken = env('TELEGRAM_BOT_TOKEN');
    }

    public function handleStartCommand($chatId, $firstName, $username = null)
    {
        $user = TelegramUser::firstOrCreate(
            ['telegram_id' => $chatId],
            [
                'first_name' => $firstName,
                'username' => $username,
                'is_vip' => false
            ]
        );

        $buttons = $this->getButtonsByUserType($user);
        $message = $this->getWelcomeMessage($firstName, $user);

        return $this->sendMessage($chatId, $message, $buttons);
    }

    private function getWelcomeMessage($firstName, $user)
    {
        $baseMessage = "Seja bem-vindo, {$firstName}! É um prazer ter você por aqui.";
        
        if ($user->email) {
            return $baseMessage . " Solicitação enviada.";
        }
        
        return $baseMessage . " O que você deseja hoje?";
    }

    private function getButtonsByUserType($user)
    {
        return $user->is_vip
            ? [
                [['text' => 'QUERO SAIR DO VIP', 'callback_data' => 'leave_vip']]
            ]
            : [
                [['text' => 'QUERO SER VIP', 'callback_data' => 'be_vip']],
                [['text' => 'ACOMPANHAR MINHA SOLICITAÇÃO', 'callback_data' => 'track']]
            ];
    }

    public function sendMessage($chatId, $message, $buttons = null)
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $message
        ];

        if ($buttons) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $buttons]);
        }

        return Http::post($this->telegramApiUrl . $this->telegramBotToken . "/sendMessage", $payload);
    }

    public function handleCallbackQuery($callbackData, $chatId)
    {
        switch ($callbackData) {
            case 'be_vip':
                return $this->handleBeVip($chatId);
            case 'leave_vip':
                return $this->handleLeaveVip($chatId);
            case 'track':
                return $this->handleTrack($chatId);
            default:
                return $this->sendMessage($chatId, "Opção não reconhecida.");
        }
    }

    public function handleMessage($chatId, $message, $firstName)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        
        if (!$user) {
            return $this->sendMessage($chatId, "Erro: Usuário não encontrado.");
        }

        if ($user->is_awaiting_email) {
            return $this->handleEmailInput($chatId, $message, $user, $firstName);
        }

        return $this->sendMessage($chatId, "Como posso ajudá-lo?");
    }

    private function handleBeVip($chatId)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        
        if (!$user) {
            return $this->sendMessage($chatId, "Erro ao processar solicitação.");
        }

        if ($user->is_vip) {
            return $this->sendMessage($chatId, "Você já é VIP!");
        }

        if ($user->email) {
            $user->update(['is_vip' => true]);
            $buttons = $this->getButtonsByUserType($user);
            $message = $this->getWelcomeMessage($user->first_name, $user);
            return $this->sendMessage($chatId, $message, $buttons);
        }

        return $this->requestEmail($chatId);
    }

    private function requestEmail($chatId)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        $user->update(['is_awaiting_email' => true]);

        return $this->sendMessage($chatId, "Por favor, informe seu e-mail para continuar:");
    }

    private function handleEmailInput($chatId, $email, $user, $firstName)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->sendMessage($chatId, "E-mail inválido. Por favor, informe um e-mail válido:");
        }

        $user->update([
            'email' => $email,
            'is_vip' => true,
            'is_awaiting_email' => false
        ]);

        $buttons = $this->getButtonsByUserType($user);
        $message = $this->getWelcomeMessage($firstName, $user);
        
        return $this->sendMessage($chatId, $message, $buttons);
    }

    private function handleLeaveVip($chatId)
    {
        $user = TelegramUser::where('telegram_id', $chatId)->first();
        
        if ($user) {
            $user->update(['is_vip' => false]);
            $buttons = $this->getButtonsByUserType($user);
            return $this->sendMessage($chatId, "Você saiu do VIP. Esperamos vê-lo novamente!", $buttons);
        }

        return $this->sendMessage($chatId, "Erro ao processar solicitação.");
    }

    private function handleTrack($chatId)
    {
        return $this->sendMessage($chatId, "Funcionalidade de acompanhamento em desenvolvimento.");
    }

    public function setWebhook()
    {
        $url = env('TELEGRAM_WEBHOOK_URL') . '/telegram/webhook';

        $response = Http::post($this->telegramApiUrl . $this->telegramBotToken . "/setWebhook", [
            'url' => $url
        ]);

        return $response->json();
    }
}