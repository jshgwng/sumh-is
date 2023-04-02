<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'save response']);
        Permission::create(['name' => 'view response reports']);
        Permission::create(['name' => 'save survey']);
        Permission::create(['name' => 'get surveys']);
        Permission::create(['name' => 'get survey']);
        Permission::create(['name' => 'delete survey']);
        Permission::create(['name' => 'get user']);

        $respondent = Role::create(['name' => 'respondent'])->givePermissionTo(['save response', 'get surveys', 'get survey']);
        $admin = Role::create(['name' => 'admin'])->givePermissionTo(['view dashboard', 'save response', 'view response reports', 'save survey', 'get surveys', 'get survey', 'delete survey']);
        $super_admin = Role::create(['name' => 'super-admin'])->givePermissionTo(Permission::all());

        $admin = User::create([
            'id' => Str::uuid(),
            'first_name' => "Super",
            'last_name' => "Admin",
            'email' => "admin@app.com",
            'username' => "admin",
            'phone' => "08012345678",
            'password' => bcrypt("password"),
        ]);

        $admin->email_verified_at = now();
        $admin->save();

        $admin->assignRole($super_admin);
    }
}