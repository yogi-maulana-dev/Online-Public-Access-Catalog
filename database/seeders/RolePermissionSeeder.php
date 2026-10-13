<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'access admin',
            'manage users',
            'manage roles',
            'manage publishers',
            'manage categories',
            'manage authors',
            'manage books',
            'publish books',
            'view submissions',
            'manage submissions',
            'assign reviewers',
            'create submission',
            'submit review',
            'view analytics',
            'manage settings',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name);
        }

        $map = [
            RoleName::SuperAdmin->value => $permissions, // also bypassed by Gate::before
            RoleName::PublisherAdmin->value => [
                'access admin', 'manage publishers', 'manage categories', 'manage authors',
                'manage books', 'publish books', 'view submissions', 'manage submissions',
                'assign reviewers', 'view analytics', 'manage settings',
            ],
            RoleName::Editor->value => [
                'access admin', 'manage authors', 'manage books', 'publish books',
                'view submissions', 'manage submissions', 'assign reviewers', 'view analytics',
            ],
            RoleName::Reviewer->value => [
                'access admin', 'view submissions', 'submit review',
            ],
            RoleName::Author->value => [
                'access admin', 'create submission',
            ],
            RoleName::Reader->value => [],
        ];

        foreach ($map as $roleName => $perms) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
