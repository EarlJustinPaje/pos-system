<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => Permission::VIEW_DASHBOARD, 'display_name' => 'View Dashboard'],
            ['name' => Permission::VIEW_POS, 'display_name' => 'View POS'],
            ['name' => Permission::VIEW_PRODUCTS, 'display_name' => 'View Products'],
            ['name' => Permission::CREATE_PRODUCTS, 'display_name' => 'Create Products'],
            ['name' => Permission::EDIT_PRODUCTS, 'display_name' => 'Edit Products'],
            ['name' => Permission::DELETE_PRODUCTS, 'display_name' => 'Delete Products'],
            ['name' => Permission::VIEW_USERS, 'display_name' => 'View Users'],
            ['name' => Permission::CREATE_USERS, 'display_name' => 'Create Users'],
            ['name' => Permission::EDIT_USERS, 'display_name' => 'Edit Users'],
            ['name' => Permission::DELETE_USERS, 'display_name' => 'Delete Users'],
            ['name' => Permission::RESET_PASSWORDS, 'display_name' => 'Reset Passwords'],
            ['name' => Permission::VIEW_REPORTS, 'display_name' => 'View Reports'],
            ['name' => Permission::VIEW_AUDIT, 'display_name' => 'View Audit Trail'],
            ['name' => Permission::MANAGE_SETTINGS, 'display_name' => 'Manage Settings'],
            ['name' => Permission::MANAGE_BRANCHES, 'display_name' => 'Manage Branches'],
            ['name' => Permission::MANAGE_SUPPLIERS, 'display_name' => 'Manage Suppliers'],
            ['name' => Permission::MANAGE_CATEGORIES, 'display_name' => 'Manage Categories'],
            ['name' => Permission::MANAGE_PROMOTIONS, 'display_name' => 'Manage Promotions'],
            ['name' => Permission::IMPORT_PRODUCTS, 'display_name' => 'Import Products'],
            ['name' => Permission::EXPORT_REPORTS, 'display_name' => 'Export Reports'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission['name']], $permission);
        }

        // Create Roles
        $adminRole = Role::firstOrCreate(
            ['name' => Role::ADMIN],
            [
                'display_name' => 'Administrator',
                'description' => 'Full system access'
            ]
        );

        $managerRole = Role::firstOrCreate(
            ['name' => Role::MANAGER],
            [
                'display_name' => 'Manager',
                'description' => 'Limited management access, cannot manage passwords or view audit'
            ]
        );

        $cashierRole = Role::firstOrCreate(
            ['name' => Role::CASHIER],
            [
                'display_name' => 'Cashier',
                'description' => 'Dashboard and POS only'
            ]
        );

        // Assign Permissions to Admin (all permissions)
        $adminRole->syncPermissions(Permission::all());

        // Assign Permissions to Manager
        $managerRole->syncPermissions([
            Permission::VIEW_DASHBOARD,
            Permission::VIEW_POS,
            Permission::VIEW_PRODUCTS,
            Permission::CREATE_PRODUCTS,
            Permission::EDIT_PRODUCTS,
            Permission::DELETE_PRODUCTS,
            Permission::VIEW_USERS,
            Permission::EDIT_USERS, // Can edit but not reset passwords
            Permission::VIEW_REPORTS,
            Permission::MANAGE_SUPPLIERS,
            Permission::MANAGE_CATEGORIES,
            Permission::MANAGE_PROMOTIONS,
            Permission::IMPORT_PRODUCTS,
            Permission::EXPORT_REPORTS,
        ]);

        // Assign Permissions to Cashier
        $cashierRole->syncPermissions([
            Permission::VIEW_DASHBOARD,
            Permission::VIEW_POS,
            Permission::VIEW_PRODUCTS, // Read-only
        ]);

        // Create default admin user if not exists
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'System Administrator',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_admin' => true,
                'role_id' => $adminRole->id,
            ]
        );

        // Create sample manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@pos.com'],
            [
                'name' => 'Store Manager',
                'username' => 'manager',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_admin' => false,
                'role_id' => $managerRole->id,
            ]
        );

        // Create sample cashier
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@pos.com'],
            [
                'name' => 'Store Cashier',
                'username' => 'cashier',
                'password' => Hash::make('password'),
                'is_active' => true,
                'is_admin' => false,
                'role_id' => $cashierRole->id,
            ]
        );

        $this->command->info('Roles and permissions seeded successfully!');
        $this->command->info('Admin: admin@pos.com / password');
        $this->command->info('Manager: manager@pos.com / password');
        $this->command->info('Cashier: cashier@pos.com / password');
    }
}