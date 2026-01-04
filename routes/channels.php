<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::routes(['middleware' => ['web', 'auth:internal']]);
// Broadcast::routes([
//     'middleware' => ['web'],
//     'guards' => ['internal', 'public'],
// ]);



// Broadcast::channel('internal-users', function ($user) {
//     \Log::info('Broadcast auth', [
//         'guard' => Auth::getDefaultDriver(),
//         'user' => $user
//     ]);
//     return true;
// }, ['guards' => ['internal']]);


Broadcast::channel('internal-user-edited.{internalUserId}', function ($user, $internalUserId) {
    \Illuminate\Support\Facades\Log::info('Broadcast auth', [
        'guard' => Auth::getDefaultDriver(),
        'isGuardInternal' => Auth::guard('internal')->check(),
        'user uuid' => $user->uuid,
        'internalUserId' => $internalUserId
    ]);
    return $user->uuid === $internalUserId;
}, ['guards' => ['internal']]);

// Broadcast::channel('internal-user.{internalUserId}', function ($user, $internalUserId) {
//     \Illuminate\Support\Facades\Log::info('Broadcast auth', [
//         'guard' => Auth::getDefaultDriver(),
//         'isGuardInternal' => Auth::guard('internal')->check(),
//         'user uuid' => $user->uuid,
//         'internalUserId' => $internalUserId
//     ]);
//     return $user->uuid === $internalUserId;
// }, ['guards' => ['internal']]);



