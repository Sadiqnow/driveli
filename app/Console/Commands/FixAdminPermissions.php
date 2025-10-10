<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AdminUser;

class FixAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'admin:fix-permissions 
                            {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     */
    protected $description = 'Fix admin user permissions based on their roles';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔧 ' . ($dryRun ? 'Checking' : 'Fixing') . ' admin permissions...');
        $this->line('');

        $admins = AdminUser::all();
        
        if ($admins->isEmpty()) {
            $this->warn('No admin users found.');
            return 0;
        }

        $this->table(
            ['Name', 'Email', 'Role', 'Current Permissions', 'Action Needed'],
            $admins->map(function ($admin) use ($dryRun) {
                $currentPermissions = $admin->getAllPermissions();
                $expectedPermissions = $this->getExpectedPermissions($admin->role);
                
                $needsUpdate = empty($currentPermissions) || 
                              !in_array('manage_users', $currentPermissions) && 
                              in_array($admin->role, ['Super Admin', 'Admin']);
                
                $action = $needsUpdate ? '⚠️ Update needed' : '✅ OK';
                
                if ($needsUpdate && !$dryRun) {
                    $admin->update(['permissions' => $expectedPermissions]);
                    $action = '✅ Updated';
                }
                
                return [
                    $admin->name,
                    $admin->email,
                    $admin->role,
                    empty($currentPermissions) ? 'None' : implode(', ', array_slice($currentPermissions, 0, 3)) . (count($currentPermissions) > 3 ? '...' : ''),
                    $action
                ];
            })->toArray()
        );

        if ($dryRun) {
            $this->line('');
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->line('');
            $this->info('✅ Admin permissions have been fixed!');
        }

        return 0;
    }

    /**
     * Get expected permissions for a role
     */
    private function getExpectedPermissions(string $role): array
    {
        switch ($role) {
            case 'Super Admin':
                return [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'manage_matches',
                    'manage_commissions',
                    'view_reports',
                    'manage_notifications',
                    'manage_settings',
                    'delete_records'
                ];
                
            case 'Admin':
                return [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'view_reports',
                    'manage_notifications'
                ];
                
            case 'Moderator':
                return [
                    'manage_drivers',
                    'manage_requests',
                    'view_reports'
                ];
                
            default:
                return ['view_reports'];
        }
    }
}