<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $resourcePermissions = [
            'view users',
            'create users',
            'update users',
            'delete users',
            'restore users',
            'force_delete users',
        ];

        foreach ($resourcePermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (['admin', 'care_seeker', 'care_giver'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Role::findByName('admin', 'web')->syncPermissions($resourcePermissions);

        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->assignRole('care_seeker');
    }
}
