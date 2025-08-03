<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    protected $fillable = [
        'telegram_id',
        'first_name',
        'username',
        'is_vip',
        'email',
        'is_awaiting_email'
    ];

    protected $casts = [
        'is_vip' => 'boolean',
        'is_awaiting_email' => 'boolean'
    ];
}
