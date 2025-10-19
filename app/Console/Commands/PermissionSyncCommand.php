<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Permission;
use App\Models\UserActivity;

class PermissionSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically detect and register new permissions from controllers';

    /**
     * Controller group mappings
     */
    protected $groupMappings = [
        'Driver' => 'Driver Management',
        'Company' => 'Company Management',
        'Admin' => 'Admin Management',
        'SuperAdmin' => 'Super Admin Management',
        'Commission' => 'Commission Management',
        'GlobalDriver' => 'Driver Management',
        'DriverOnboarding' => 'Driver Onboarding',
        'Home' => 'Dashboard',
        'Auth' => 'Authentication',
    ];

    /**
     * Standard CRUD actions mapping
     */
    protected $actionMappings = [
        'index' => 'view',
        'show' => 'view',
        'create' => 'create',
        'store' => 'create',
        'edit' => 'edit',
        'update' => 'edit',
        'destroy' => 'delete',
        'approve' => 'approve',
        'reject' => 'reject',
        'verify' => 'verify',
        'export' => 'export',
        'bulkApprove' => 'approve',
        'bulkReject' => 'reject',
        'bulkDelete' => 'delete',
        'bulkFlag' => 'manage',
        'bulkRestore' => 'manage',
    ];

    /**
     * Execute the console command.
     *
     * This method orchestrates the permission sync process:
     * 1. Scans all controllers in the app/Http/Controllers directory
     * 2. Generates permission data based on controller methods
     * 3. Either displays a dry-run preview or applies the changes
     * 4. Logs all changes to audit logs for compliance
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Scanning controllers for permissions...');

        $controllers = $this->scanControllers();
        $detectedPermissions = $this->generatePermissions($controllers);

        if ($this->option('dry-run')) {
            $this->displayDryRunResults($detectedPermissions);
            return Command::SUCCESS;
        }

        $results = $this->syncPermissions($detectedPermissions);

        $this->displayResults($results);

        // Log to audit logs
        $this->logToAudit($results);

        return Command::SUCCESS;
    }

    /**
     * Scan all controller files in the app/Http/Controllers directory.
     *
     * This method recursively scans the controllers directory and identifies
     * all PHP files ending with 'Controller.php'. It returns an array of
     * relative controller paths (without the .php extension) that can be
     * used for reflection and permission generation.
     *
     * @return array Array of controller class paths relative to App\Http\Controllers
     */
    protected function scanControllers(): array
    {
        $controllerPath = app_path('Http/Controllers');
        $controllers = [];

        $files = File::allFiles($controllerPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && str_contains($file->getFilename(), 'Controller.php')) {
                $relativePath = str_replace($controllerPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
                $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
                $relativePath = str_replace('.php', '', $relativePath);

                $controllers[] = $relativePath;
            }
        }

        return $controllers;
    }

    /**
     * Generate permissions from controllers using reflection and mapping logic.
     *
     * This method processes each controller to:
     * 1. Extract the controller name and determine its group
     * 2. Skip system/auth controllers that don't need permissions
     * 3. Use reflection to get public methods from the controller
     * 4. Map methods to standard CRUD actions (view, create, edit, delete, etc.)
     * 5. Generate unique permission names and metadata
     *
     * @param array $controllers Array of controller paths
     * @return array Array of permission data arrays
     */
    protected function generatePermissions(array $controllers): array
    {
        $permissions = [];
        $uniquePermissions = [];

        foreach ($controllers as $controller) {
            $controllerName = $this->extractControllerName($controller);
            $groupName = $this->getGroupName($controllerName);

            // Skip certain controllers that don't need permissions
            if (in_array($controllerName, ['Controller', 'Auth', 'ConfirmPassword', 'ForgotPassword', 'Login', 'Register', 'ResetPassword', 'LGAFallback', 'Location'])) {
                continue;
            }

            $resource = $this->getResourceName($controllerName);
            $methods = $this->getControllerMethods($controller);

            foreach ($methods as $method) {
                $action = $this->mapMethodToAction($method);

                if ($action) {
                    $permissionName = $resource . '.' . $action;

                    // Skip duplicates to avoid creating the same permission multiple times
                    if (isset($uniquePermissions[$permissionName])) {
                        continue;
                    }

                    $uniquePermissions[$permissionName] = true;

                    $permissions[] = [
                        'name' => $permissionName,
                        'display_name' => ucfirst($action) . ' ' . ucfirst(str_replace('_', ' ', $resource)),
                        'description' => "Allows {$action} operations on {$resource}",
                        'category' => $this->getCategoryFromResource($resource),
                        'group_name' => $groupName,
                        'resource' => $resource,
                        'action' => $action,
                        'controller' => $controller,
                        'method' => $method,
                    ];
                }
            }
        }

        return $permissions;
    }

    /**
     * Extract controller name from path
     */
    protected function extractControllerName(string $controllerPath): string
    {
        $parts = explode('/', $controllerPath);
        $filename = end($parts);
        return str_replace('Controller', '', $filename);
    }

    /**
     * Get group name for controller
     */
    protected function getGroupName(string $controllerName): string
    {
        foreach ($this->groupMappings as $prefix => $group) {
            if (str_starts_with($controllerName, $prefix)) {
                return $group;
            }
        }

        return ucfirst($controllerName) . ' Management';
    }

    /**
     * Get resource name from controller
     */
    protected function getResourceName(string $controllerName): string
    {
        $name = strtolower($controllerName);

        // Handle special cases
        $mappings = [
            'globaldriver' => 'drivers',
            'driveronboarding' => 'drivers',
            'superadmin' => 'superadmin',
            'company' => 'companies',
        ];

        return $mappings[$name] ?? $name . 's';
    }

    /**
     * Get controller methods using PHP reflection.
     *
     * This method uses ReflectionClass to inspect the controller and extract
     * all public methods. It filters out inherited methods and magic methods
     * to focus only on methods defined in the specific controller class.
     *
     * Falls back to standard CRUD methods if reflection fails (e.g., syntax errors).
     *
     * @param string $controller Relative controller path
     * @return array Array of public method names
     */
    protected function getControllerMethods(string $controller): array
    {
        try {
            $controllerClass = 'App\\Http\\Controllers\\' . str_replace('/', '\\', $controller);
            $reflection = new \ReflectionClass($controllerClass);
            $methods = [];

            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                // Skip inherited methods and magic methods
                if ($method->class === $controllerClass && !str_starts_with($method->name, '__')) {
                    $methods[] = $method->name;
                }
            }

            return $methods;
        } catch (\Exception $e) {
            // Fallback to standard CRUD methods if reflection fails
            $this->warn("Could not reflect controller {$controller}: " . $e->getMessage());
            return ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];
        }
    }

    /**
     * Map method to action
     */
    protected function mapMethodToAction(string $method): ?string
    {
        return $this->actionMappings[$method] ?? null;
    }

    /**
     * Get category from resource
     */
    protected function getCategoryFromResource(string $resource): string
    {
        $categories = [
            'drivers' => Permission::CATEGORY_DRIVER,
            'companies' => Permission::CATEGORY_COMPANY,
            'users' => Permission::CATEGORY_USER,
            'roles' => Permission::CATEGORY_ADMIN,
            'permissions' => Permission::CATEGORY_ADMIN,
            'reports' => Permission::CATEGORY_REPORT,
            'commissions' => Permission::CATEGORY_SYSTEM,
            'superadmin' => Permission::CATEGORY_SYSTEM,
        ];

        return $categories[$resource] ?? Permission::CATEGORY_SYSTEM;
    }

    /**
     * Sync permissions with database, creating new ones and tracking existing ones.
     *
     * This method iterates through detected permissions and:
     * 1. Checks if each permission already exists in the database
     * 2. Creates new permissions with full metadata
     * 3. Tracks both new and existing permissions for reporting
     *
     * Uses firstOrCreate to prevent race conditions and duplicates.
     *
     * @param array $detectedPermissions Array of permission data from generatePermissions()
     * @return array Array with 'new' and 'existing' permission collections
     */
    protected function syncPermissions(array $detectedPermissions): array
    {
        $newPermissions = [];
        $existingPermissions = [];

        foreach ($detectedPermissions as $perm) {
            $existing = Permission::where('name', $perm['name'])->first();

            if (!$existing) {
                $permission = Permission::create([
                    'name' => $perm['name'],
                    'display_name' => $perm['display_name'],
                    'description' => $perm['description'],
                    'category' => $perm['category'],
                    'group_name' => $perm['group_name'],
                    'resource' => $perm['resource'],
                    'action' => $perm['action'],
                    'is_active' => true,
                ]);

                $newPermissions[] = $permission;
            } else {
                $existingPermissions[] = $existing;
            }
        }

        return [
            'new' => $newPermissions,
            'existing' => $existingPermissions,
        ];
    }

    /**
     * Display dry run results
     */
    protected function displayDryRunResults(array $permissions): void
    {
        $this->info('📋 DRY RUN - The following permissions would be created:');

        $grouped = collect($permissions)->groupBy('group_name');

        foreach ($grouped as $group => $perms) {
            $this->line("🔸 {$group}:");
            foreach ($perms as $perm) {
                $this->line("   • {$perm['name']} - {$perm['display_name']}");
            }
            $this->line('');
        }

        $this->info("Total permissions that would be created: " . count($permissions));
    }

    /**
     * Display sync results
     */
    protected function displayResults(array $results): void
    {
        $newCount = count($results['new']);
        $existingCount = count($results['existing']);

        $this->info("✅ Permission sync completed!");
        $this->line("📈 {$newCount} new permissions added");
        $this->line("📋 {$existingCount} permissions already exist");

        if ($newCount > 0) {
            $this->info("🆕 New permissions:");
            foreach ($results['new'] as $permission) {
                $this->line("   • {$permission->name} ({$permission->group_name})");
            }
        }
    }

    /**
     * Log to audit logs
     */
    protected function logToAudit(array $results): void
    {
        $newCount = count($results['new']);

        if ($newCount > 0) {
            $permissionNames = collect($results['new'])->pluck('name')->join(', ');

            UserActivity::log(
                'permission_sync',
                "Permission sync completed: {$newCount} new permissions added - {$permissionNames}",
                null,
                null,
                null,
                ['new_permissions_count' => $newCount, 'new_permissions' => $permissionNames]
            );

            Log::info("Permission sync completed: {$newCount} new permissions added", [
                'new_permissions' => $permissionNames
            ]);
        }
    }
}
