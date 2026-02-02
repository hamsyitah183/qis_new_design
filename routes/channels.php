<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Broadcast::routes(['middleware' => ['web', 'auth:internal']]);
Broadcast::routes(['middleware' => ['web']]);

Broadcast::channel('internal-users', function ($user) {
    \Illuminate\Support\Facades\Log::info('Internal users listening', [
        'guard' => Auth::getDefaultDriver(),
        'user_uuid' => $user->uuid,
        'make application'
    ]);
    return true;
}, ['guards' => ['internal']]);

Broadcast::channel('internal-admins', function ($user) {
    // Ensure internal guard
    if (Auth::guard('internal')->check() && ($user->hasRole('admin') || $user->hasRole('superadmin'))) {
        return true;
    }

    return false;
}, ['guards' => ['internal']]);

Broadcast::channel('internal-clerks', function ($user) {
    // Ensure internal guard
    if (Auth::guard('internal')->check() && $user->hasRole('clerk')) {
        return true;
    }

    return false;
}, ['guards' => ['internal']]);

Broadcast::channel('internal-officer', function ($user) {
    // Ensure internal guard
    if (Auth::guard('internal')->check() && $user->hasRole('officer')) {
        return true;
    }

    return false;
}, ['guards' => ['internal']]);

Broadcast::channel('internal-user-edited.{internalUserId}', function ($user, $internalUserId) {
    \Illuminate\Support\Facades\Log::info('Broadcast auth', [
        'guard' => Auth::getDefaultDriver(),
        'isGuardInternal' => Auth::guard('internal')->check(),
        'user uuid' => $user->uuid,
        'internalUserId' => $internalUserId
    ]);
    return $user->uuid === $internalUserId;
}, ['guards' => ['internal']]);


Broadcast::channel('public-user.{uuid}', function ($user, $uuid) {
    \Illuminate\Support\Facades\Log::info('Broadcast auth', [
        'guard' => Auth::getDefaultDriver(),
        'isGuardPublic' => Auth::guard('public')->check(),
        'user uuid' => $user->uuid,
        'uuid' => $uuid
    ]);
    return Auth::guard('public')->check()
        && $user->uuid === $uuid;
}, ['guards' => ['public']]);



// Broadcast::channel('internal-user.{internalUserId}', function ($user, $internalUserId) {
//     \Illuminate\Support\Facades\Log::info('Broadcast auth', [
//         'guard' => Auth::getDefaultDriver(),
//         'isGuardInternal' => Auth::guard('internal')->check(),
//         'user uuid' => $user->uuid,
//         'internalUserId' => $internalUserId
//     ]);
//     return $user->uuid === $internalUserId;
// }, ['guards' => ['internal']]);



