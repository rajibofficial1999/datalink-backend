<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('AccountInfoPrivateChannel.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
}, ['guards' => ['sanctum']]);
