<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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

        $admin = User::updateOrCreate([
            'email' => 'admin@mail.com',
        ], [
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['admin']);

        $careSeeker = User::updateOrCreate([
            'email' => 'care_seeker@mail.com',
        ], [
            'name' => 'Care Seeker',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $careSeeker->profile()->updateOrCreate([], [
            'first_name' => 'Care',
            'last_name' => 'Seeker',
            'age' => 30,
            'phone' => '00000000',
            'address' => 'Care Seeker Address',
            'city' => 'Port Louis',
        ]);

        $careSeeker->careSeekerProfile()->updateOrCreate([], [
            'care_for' => 'Myself',
            'care_needs' => 'Home care support',
            'is_active' => true,
        ]);

        $careSeeker->syncRoles(['care_seeker']);

        $careGiver = User::updateOrCreate([
            'email' => 'care_giver@mail.com',
        ], [
            'name' => 'Care Giver',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $careGiver->profile()->updateOrCreate([], [
            'first_name' => 'Care',
            'last_name' => 'Giver',
            'age' => 35,
            'phone' => '57000001',
            'address' => 'Caregiver Address',
            'city' => 'Curepipe',
        ]);

        $careGiver->careGiverProfile()->updateOrCreate([], [
            'type' => 'nurse',
            'is_active' => true,
        ]);

        $careGiver->assignRole('care_giver');
    }
}
