<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::where('name', 'Admin')->first();
        $user = Role::where('name', 'User')->first();

        $allPermissions = Permission::all()->pluck('id');
        $basicPermissions = Permission::whereIn('name', ['create_transaction', 'view_transaction'])->pluck('id');

        $admin->permissions()->sync($allPermissions);
        $user->permissions()->sync($basicPermissions);
    }
}
