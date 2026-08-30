<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\InternalUser;
use Laravel\Sanctum\PersonalAccessToken;

// Clear all scanner-app tokens for every internal user.
// Effect: every app session revoked → next API call returns 401 → app logs out.
$tokens = PersonalAccessToken::where('name', 'scanner-app')->get();

echo 'Before: ', $tokens->count(), ' app token(s)', PHP_EOL;

foreach ($tokens as $token) {
    $user = InternalUser::find($token->tokenable_id);
    echo '  revoking: ', $user?->email ?? $token->tokenable_id, ' (login ', $token->created_at, ')', PHP_EOL;
    $token->delete();
}

echo 'After: ', PersonalAccessToken::where('name', 'scanner-app')->count(), ' app token(s)', PHP_EOL;
echo 'Done. All app sessions revoked.', PHP_EOL;
