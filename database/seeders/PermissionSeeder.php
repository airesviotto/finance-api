<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $permissions = [
            ['name' => 'create_transaction', 'description' => 'Can create transactions'],
            ['name' => 'view_transaction', 'description' => 'Can view transactions'],
            ['name' => 'delete_transaction', 'description' => 'Can delete transactions'],
            ['name' => 'manage_users', 'description' => 'Can manage users'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
