<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permission = Permission::create(['name' => 'manage roadmaps']);

        // 2. Create Roles
        $adminRole = Role::create(['name' => 'admin']);
        $studentRole = Role::create(['name' => 'student']);

        // 3. Assign Permissions to Roles (Optional: Give admin specific permissions)
        $adminRole->givePermissionTo($permission);

        // 4. Create Admin User
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@devnexus.com',
        ]);
        $admin->assignRole($adminRole);

        // 5. Create Student User
        $student = User::factory()->create([
            'name' => 'Student',
            'email' => 'student@devnexus.com',
        ]);
        $student->assignRole($studentRole);
    }
}
