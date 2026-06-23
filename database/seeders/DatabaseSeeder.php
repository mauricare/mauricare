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

        $resourcePermissions = collect([
            'users',
            'user_profiles',
            'care_giver_profiles',
            'care_seeker_profiles',
            'documents',
        ])->flatMap(fn (string $resource) => [
            "view {$resource}",
            "create {$resource}",
            "update {$resource}",
            "delete {$resource}",
            "restore {$resource}",
            "force_delete {$resource}",
        ])->all();

        $permissions = collect($resourcePermissions)
            ->map(fn (string $permission) => Permission::findOrCreate($permission, 'web'));

        foreach (['admin', 'care_seeker', 'care_giver'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        Role::findByName('admin', 'web')->syncPermissions($permissions);

        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->profile()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'age' => 30,
            'phone' => '00000000',
            'address' => 'Test Address',
            'city' => 'Port Louis',
        ]);

        $user->careSeekerProfile()->create([
            'care_for' => 'Myself',
            'care_needs' => 'Test care needs',
        ]);

        $user->assignRole('care_seeker');
    }
}
