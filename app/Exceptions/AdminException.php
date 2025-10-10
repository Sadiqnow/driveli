<?php

namespace App\Exceptions;

use Exception;

class AdminException extends Exception
{
    /**
     * Admin not found exception.
     *
     * @param string $identifier
     * @return static
     */
    public static function notFound(string $identifier): static
    {
        return new static("Admin with identifier '{$identifier}' not found.");
    }

    /**
     * Admin account inactive exception.
     *
     * @param string $adminId
     * @return static
     */
    public static function accountInactive(string $adminId): static
    {
        return new static("Admin account '{$adminId}' is inactive.");
    }

    /**
     * Admin account suspended exception.
     *
     * @param string $adminId
     * @param string $reason
     * @return static
     */
    public static function accountSuspended(string $adminId, string $reason = null): static
    {
        $message = "Admin account '{$adminId}' is suspended";
        if ($reason) {
            $message .= ": {$reason}";
        }
        return new static($message);
    }

    /**
     * Admin insufficient permissions exception.
     *
     * @param string $adminId
     * @param string $permission
     * @return static
     */
    public static function insufficientPermissions(string $adminId, string $permission): static
    {
        return new static("Admin '{$adminId}' does not have permission: {$permission}");
    }

    /**
     * Admin invalid credentials exception.
     *
     * @return static
     */
    public static function invalidCredentials(): static
    {
        return new static("Invalid admin credentials provided.");
    }

    /**
     * Admin email already exists exception.
     *
     * @param string $email
     * @return static
     */
    public static function emailExists(string $email): static
    {
        return new static("An admin with email '{$email}' already exists.");
    }

    /**
     * Admin phone already exists exception.
     *
     * @param string $phone
     * @return static
     */
    public static function phoneExists(string $phone): static
    {
        return new static("An admin with phone number '{$phone}' already exists.");
    }

    /**
     * Admin cannot delete self exception.
     *
     * @param string $adminId
     * @return static
     */
    public static function cannotDeleteSelf(string $adminId): static
    {
        return new static("Admin '{$adminId}' cannot delete their own account.");
    }

    /**
     * Admin cannot modify super admin exception.
     *
     * @param string $action
     * @return static
     */
    public static function cannotModifySuperAdmin(string $action): static
    {
        return new static("Cannot {$action} Super Admin account.");
    }

    /**
     * Admin super admin already exists exception.
     *
     * @return static
     */
    public static function superAdminExists(): static
    {
        return new static("A Super Admin already exists in the system.");
    }

    /**
     * Admin weak password exception.
     *
     * @param array $requirements
     * @return static
     */
    public static function weakPassword(array $requirements): static
    {
        $requirementText = implode(', ', $requirements);
        return new static("Password does not meet requirements: {$requirementText}");
    }

    /**
     * Admin session expired exception.
     *
     * @return static
     */
    public static function sessionExpired(): static
    {
        return new static("Admin session has expired. Please log in again.");
    }

    /**
     * Admin too many login attempts exception.
     *
     * @param int $minutes
     * @return static
     */
    public static function tooManyLoginAttempts(int $minutes): static
    {
        return new static("Too many login attempts. Please try again in {$minutes} minutes.");
    }

    /**
     * Admin password mismatch exception.
     *
     * @return static
     */
    public static function passwordMismatch(): static
    {
        return new static("Current password is incorrect.");
    }

    /**
     * Admin role assignment error exception.
     *
     * @param string $role
     * @param string $reason
     * @return static
     */
    public static function roleAssignmentError(string $role, string $reason): static
    {
        return new static("Cannot assign role '{$role}': {$reason}");
    }

    /**
     * Admin permission assignment error exception.
     *
     * @param string $permission
     * @param string $reason
     * @return static
     */
    public static function permissionAssignmentError(string $permission, string $reason): static
    {
        return new static("Cannot assign permission '{$permission}': {$reason}");
    }

    /**
     * Admin operation not allowed exception.
     *
     * @param string $operation
     * @param string $reason
     * @return static
     */
    public static function operationNotAllowed(string $operation, string $reason): static
    {
        return new static("Operation '{$operation}' is not allowed: {$reason}");
    }
}