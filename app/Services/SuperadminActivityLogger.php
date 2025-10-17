<?php

namespace App\Services;

use App\Models\SuperadminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class SuperadminActivityLogger
{
    /**
     * Log a Superadmin activity
     */
    public static function log(
        string $action,
        string $description,
        Model $resource = null,
        array $oldValues = null,
        array $newValues = null,
        array $metadata = null,
        Request $request = null
    ): SuperadminActivityLog {
        $superadmin = auth("admin")->user();

        // Allow logging even without authenticated superadmin for system operations
        if (!$superadmin) {
            // Create a system activity log entry
            $logData = [
                "superadmin_id" => null, // System operation
                "action" => $action,
                "description" => "[SYSTEM] " . $description,
                "ip_address" => $request ? $request->ip() : request()->ip(),
                "user_agent" => $request ? $request->userAgent() : request()->userAgent(),
            ];
        } else {
            $logData = [
                "superadmin_id" => $superadmin->id,
                "action" => $action,
                "description" => $description,
                "ip_address" => $request ? $request->ip() : request()->ip(),
                "user_agent" => $request ? $request->userAgent() : request()->userAgent(),
            ];
        }

        if ($resource) {
            $logData["resource_type"] = self::getResourceType($resource);
            $logData["resource_id"] = $resource->getKey();
            $logData["resource_name"] = self::getResourceName($resource);
        }

        if ($oldValues) {
            $logData["old_values"] = $oldValues;
        }

        if ($newValues) {
            $logData["new_values"] = $newValues;
        }

        if ($metadata) {
            $logData["metadata"] = $metadata;
        }

        return SuperadminActivityLog::create($logData);
    }

    /**
     * Log driver creation
     */
    public static function logDriverCreation(Model $driver, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "create",
            "Created new driver: {$driver->full_name} ({$driver->driver_id})",
            $driver,
            null,
            [
                "driver_id" => $driver->driver_id,
                "first_name" => $driver->first_name,
                "surname" => $driver->surname,
                "email" => $driver->email,
                "phone" => $driver->phone,
                "status" => $driver->status,
                "verification_status" => $driver->verification_status,
            ],
            ["creation_method" => "superadmin_direct"],
            $request
        );
    }

    /**
     * Log driver update
     */
    public static function logDriverUpdate(Model $driver, array $oldValues, array $newValues, Request $request = null): SuperadminActivityLog
    {
        $changes = self::getChangesDescription($oldValues, $newValues);

        return self::log(
            "update",
            "Updated driver {$driver->full_name}: {$changes}",
            $driver,
            $oldValues,
            $newValues,
            null,
            $request
        );
    }

    /**
     * Log driver deletion
     */
    public static function logDriverDeletion(Model $driver, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "delete",
            "Deleted driver: {$driver->full_name} ({$driver->driver_id})",
            $driver,
            [
                "driver_id" => $driver->driver_id,
                "first_name" => $driver->first_name,
                "surname" => $driver->surname,
                "email" => $driver->email,
                "status" => $driver->status,
            ],
            null,
            ["deletion_type" => "soft_delete"],
            $request
        );
    }

    /**
     * Log driver approval
     */
    public static function logDriverApproval(Model $driver, string $notes = null, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "approve",
            "Approved driver application: {$driver->full_name} ({$driver->driver_id})",
            $driver,
            ["verification_status" => $driver->getOriginal("verification_status")],
            ["verification_status" => "verified", "verified_at" => now()],
            $notes ? ["approval_notes" => $notes] : null,
            $request
        );
    }

    /**
     * Log driver rejection
     */
    public static function logDriverRejection(Model $driver, string $reason, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "reject",
            "Rejected driver application: {$driver->full_name} ({$driver->driver_id}) - Reason: {$reason}",
            $driver,
            ["verification_status" => $driver->getOriginal("verification_status")],
            ["verification_status" => "rejected", "rejection_reason" => $reason],
            ["rejection_reason" => $reason],
            $request
        );
    }

    /**
     * Log driver flagging
     */
    public static function logDriverFlagging(Model $driver, string $reason, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "flag",
            "Flagged driver: {$driver->full_name} ({$driver->driver_id}) - Reason: {$reason}",
            $driver,
            ["status" => $driver->getOriginal("status")],
            ["status" => "flagged"],
            ["flag_reason" => $reason],
            $request
        );
    }

    /**
     * Log verification action activity
     */
    public static function logVerificationAction(Model $driver, string $action, $admin, string $notes = null): SuperadminActivityLog
    {
        $adminName = is_object($admin) ? ($admin->name ?? $admin->email) : 'System';

        return self::log(
            "verification",
            "Verification action '{$action}' performed on driver {$driver->full_name} by {$adminName}",
            $driver,
            ["verification_status" => $driver->getOriginal("verification_status")],
            ["verification_status" => $action === 'verify' ? 'verified' : ($action === 'reject' ? 'rejected' : 'pending')],
            array_filter([
                "action" => $action,
                "admin_name" => $adminName,
                "notes" => $notes,
            ]),
            request()
        );
    }

    /**
     * Log bulk action activity
     */
    public static function logBulkAction(Model $driver, string $action, $admin, string $notes = null): SuperadminActivityLog
    {
        $adminName = is_object($admin) ? ($admin->name ?? $admin->email) : 'System';

        return self::log(
            "bulk_action",
            "Bulk action '{$action}' performed on driver {$driver->full_name} by {$adminName}",
            $driver,
            null,
            null,
            array_filter([
                "action" => $action,
                "admin_name" => $adminName,
                "notes" => $notes,
                "bulk_operation" => true,
            ]),
            request()
        );
    }

    /**
     * Log document action activity
     */
    public static function logDocumentAction($document, string $action, $admin, string $notes = null): SuperadminActivityLog
    {
        $adminName = is_object($admin) ? ($admin->name ?? $admin->email) : 'System';
        $driver = $document->driver ?? null;

        return self::log(
            "document_action",
            "Document action '{$action}' performed on {$document->document_type} for driver " . ($driver ? $driver->full_name : 'Unknown') . " by {$adminName}",
            $document,
            ["verification_status" => $document->getOriginal("verification_status")],
            ["verification_status" => $action === 'approve' ? 'approved' : 'rejected'],
            array_filter([
                "action" => $action,
                "document_type" => $document->document_type,
                "admin_name" => $adminName,
                "notes" => $notes,
            ]),
            request()
        );
    }

    /**
     * Log driver restoration
     */
    public static function logDriverRestoration(Model $driver, Request $request = null): SuperadminActivityLog
    {
        return self::log(
            "restore",
            "Restored driver: {$driver->full_name} ({$driver->driver_id})",
            $driver,
            ["status" => $driver->getOriginal("status")],
            ["status" => "active"],
            ["restoration_type" => "status_restore"],
            $request
        );
    }

    /**
     * Log bulk operation
     */
    public static function logBulkOperation(string $operation, string $resourceType, array $resourceIds, array $metadata = null, Request $request = null): SuperadminActivityLog
    {
        $count = count($resourceIds);

        return self::log(
            "bulk_operation",
            "Performed bulk {$operation} on {$count} {$resourceType}(s)",
            null,
            null,
            null,
            array_merge($metadata ?? [], [
                "operation" => $operation,
                "resource_type" => $resourceType,
                "affected_ids" => $resourceIds,
                "count" => $count,
            ]),
            $request
        );
    }

    /**
     * Get resource type from model
     */
    private static function getResourceType(Model $resource): string
    {
        return match(get_class($resource)) {
            "App\Models\Drivers" => "driver",
            "App\Models\AdminUser" => "user",
            "App\Models\Company" => "company",
            "App\Models\CompanyRequest" => "company_request",
            default => strtolower(class_basename($resource)),
        };
    }

    /**
     * Get human-readable resource name
     */
    private static function getResourceName(Model $resource): ?string
    {
        return match(get_class($resource)) {
            "App\Models\Drivers" => $resource->full_name ?? $resource->driver_id,
            "App\Models\AdminUser" => $resource->name ?? $resource->email,
            "App\Models\Company" => $resource->name ?? $resource->company_id,
            "App\Models\CompanyRequest" => $resource->request_id ?? "Request #{$resource->id}",
            default => $resource->name ?? $resource->title ?? "ID: {$resource->getKey()}",
        };
    }

    /**
     * Generate description of changes
     */
    private static function getChangesDescription(array $oldValues, array $newValues): string
    {
        $changes = [];

        foreach ($newValues as $field => $newValue) {
            $oldValue = $oldValues[$field] ?? null;

            if ($oldValue !== $newValue) {
                $fieldName = ucfirst(str_replace("_", " ", $field));
                $changes[] = "{$fieldName}: \"{$oldValue}\" → \"{$newValue}\"";
            }
        }

        return implode(", ", $changes);
    }

    /**
     * Get activity summary for a Superadmin
     */
    public static function getActivitySummary(int $superadminId, int $days = 30): array
    {
        return SuperadminActivityLog::getActivitySummary($superadminId, $days);
    }

    /**
     * Get recent activities for dashboard
     */
    public static function getRecentActivities(int $superadminId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return SuperadminActivityLog::where("superadmin_id", $superadminId)
            ->with("superadmin:id,name,email")
            ->orderBy("created_at", "desc")
            ->limit($limit)
            ->get();
    }
}
