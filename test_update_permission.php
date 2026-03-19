<?php
$req = \Illuminate\Http\Request::create('/internal/permission/update', 'POST', [
    'role' => 'boundary officer',
    // 'permission' is missing entirely
]);

// Need to bypass auth middleware since we are not logged in, but we can just call the controller method directly.
$controller = app()->make(\App\Http\Controllers\RoleAndPermissionController::class);

try {
    // Mock the validate method behavior or let it run
    $response = $controller->update_permission($req);
    echo "Response:\n";
    echo $response->getContent();
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation failed:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Exception:\n";
    echo $e->getMessage() . "\n" . $e->getTraceAsString();
}
