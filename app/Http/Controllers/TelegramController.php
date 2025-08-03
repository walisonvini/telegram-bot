<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\ProcessTelegramWebhook;

class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        ProcessTelegramWebhook::dispatch($request->all());

        return response()->json(['ok' => true]);
    }
}
