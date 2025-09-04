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
            ['name' => 'view_all_transactions', 'description' => 'Can view all transactions'],
            ['name' => 'create_transaction', 'description' => 'Can create transactions'],
            ['name' => 'update_transaction', 'description' => 'Can update transactions'],
            ['name' => 'view_transaction', 'description' => 'Can view transactions'],
            ['name' => 'delete_transaction', 'description' => 'Can delete transactions'],

            ['name' => 'view_all_categories', 'description' => 'Can view all categories'],
            ['name' => 'create_category', 'description' => 'Can create transactions'],
            ['name' => 'update_category', 'description' => 'Can update transactions'],
            ['name' => 'view_category', 'description' => 'Can view transactions'],
            ['name' => 'delete_category', 'description' => 'Can delete transactions'],

            ['name' => 'manage_users', 'description' => 'Can manage users'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
