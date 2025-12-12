<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            // Tenant management
            'manage_tenant',
            'view_tenant_settings',
            'edit_tenant_settings',
            
            // User management
            'manage_users',
            'view_users',
            'create_users',
            'edit_users',
            'delete_users',
            
            // Client management
            'manage_clients',
            'view_clients',
            'create_clients',
            'edit_clients',
            'delete_clients',
            
            // Shipment management
            'manage_shipments',
            'view_shipments',
            'create_shipments',
            'edit_shipments',
            'delete_shipments',
            'track_shipments',
            
            // Financial management
            'manage_financial',
            'view_invoices',
            'create_invoices',
            'edit_invoices',
            'delete_invoices',
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',
            
            // Reports
            'view_reports',
            'export_reports',
            
            // Super Admin
            'super_admin',
            'manage_platform',
            'view_platform_stats',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'Super Admin' => [
                'super_admin',
                'manage_platform',
                'view_platform_stats',
            ],
            'Admin Tenant' => [
                'manage_tenant',
                'view_tenant_settings',
                'edit_tenant_settings',
                'manage_users',
                'view_users',
                'create_users',
                'edit_users',
                'delete_users',
                'manage_clients',
                'view_clients',
                'create_clients',
                'edit_clients',
                'delete_clients',
                'manage_shipments',
                'view_shipments',
                'create_shipments',
                'edit_shipments',
                'delete_shipments',
                'track_shipments',
                'manage_financial',
                'view_invoices',
                'create_invoices',
                'edit_invoices',
                'delete_invoices',
                'view_expenses',
                'create_expenses',
                'edit_expenses',
                'delete_expenses',
                'view_reports',
                'export_reports',
            ],
            'Financeiro' => [
                'view_shipments',
                'track_shipments',
                'manage_financial',
                'view_invoices',
                'create_invoices',
                'edit_invoices',
                'delete_invoices',
                'view_expenses',
                'create_expenses',
                'edit_expenses',
                'delete_expenses',
                'view_reports',
                'export_reports',
            ],
            'Operacional' => [
                'view_clients',
                'create_clients',
                'edit_clients',
                'manage_shipments',
                'view_shipments',
                'create_shipments',
                'edit_shipments',
                'track_shipments',
                'view_reports',
            ],
            'Vendedor' => [
                'view_clients',
                'create_clients',
                'edit_clients',
                'view_shipments',
                'create_shipments',
                'track_shipments',
            ],
            'Driver' => [
                'view_shipments',
                'track_shipments',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::create(['name' => $roleName]);
            $role->givePermissionTo($rolePermissions);
        }
    }
}























