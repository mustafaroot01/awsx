<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check all roles and whether they have read.ProductionPlan
$perm = \App\Models\Permission::where('slug', 'read.ProductionPlan')->first();
echo "Permission 'read.ProductionPlan' ID: " . ($perm ? $perm->id : 'NOT FOUND') . "\n\n";

$roles = \App\Models\Role::with('permissions')->get();
foreach ($roles as $role) {
    $hasPerm = $perm && $role->permissions->contains('id', $perm->id);
    echo "[" . ($hasPerm ? '✓' : '✗') . "] {$role->name}\n";
}

echo "\n--- Users and their roles ---\n";
$users = \App\Models\User::with('role')->get();
foreach ($users as $user) {
    echo "User: {$user->name} | Role: " . ($user->role?->name ?? 'NO ROLE') . " | branch_id: " . ($user->branch_id ?? 'null') . "\n";
}
