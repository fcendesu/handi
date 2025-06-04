<?php

namespace App\Services;

use App\Models\TransactionLog;
use App\Models\Discovery;
use App\Models\User;
use App\Models\Item;
use App\Models\Property;
use App\Models\WorkGroup;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionLogService
{
    public static function logDiscoveryCreated(Discovery $discovery, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_CREATED,
            'new_values' => [
                'customer_name' => $discovery->customer_name,
                'status' => $discovery->status,
                'total_cost' => $discovery->total_cost,
            ],
            'metadata' => [
                'customer_email' => $discovery->customer_email,
                'customer_phone' => $discovery->customer_phone,
            ]
        ]);
    }

    public static function logStatusChange(Discovery $discovery, string $oldStatus, string $newStatus, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_STATUS_CHANGED,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'metadata' => [
                'customer_name' => $discovery->customer_name,
            ]
        ]);
    }

    public static function logCustomerApproval(Discovery $discovery, string $customerEmail): void
    {
        self::createLog([
            'user_id' => null,
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_APPROVED,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'in_progress'],
            'performed_by_type' => TransactionLog::PERFORMER_CUSTOMER,
            'performed_by_identifier' => $customerEmail,
            'metadata' => [
                'customer_name' => $discovery->customer_name,
                'approval_method' => 'shared_link',
            ]
        ]);
    }

    public static function logCustomerRejection(Discovery $discovery, string $customerEmail): void
    {
        self::createLog([
            'user_id' => null,
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_REJECTED,
            'old_values' => ['status' => 'pending'],
            'new_values' => ['status' => 'cancelled'],
            'performed_by_type' => TransactionLog::PERFORMER_CUSTOMER,
            'performed_by_identifier' => $customerEmail,
            'metadata' => [
                'customer_name' => $discovery->customer_name,
                'rejection_method' => 'shared_link',
            ]
        ]);
    }

    public static function logAssignment(Discovery $discovery, User $assignee, ?User $assigner = null): void
    {
        self::createLog([
            'user_id' => $assigner?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_ASSIGNED,
            'new_values' => [
                'assignee_id' => $assignee->id,
                'assignee_name' => $assignee->name,
            ],
            'metadata' => [
                'customer_name' => $discovery->customer_name,
                'assignment_type' => $assigner ? 'manual' : 'self_assignment',
            ]
        ]);
    }

    public static function logUnassignment(Discovery $discovery, User $previousAssignee, ?User $unassigner = null): void
    {
        self::createLog([
            'user_id' => $unassigner?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_UNASSIGNED,
            'old_values' => [
                'assignee_id' => $previousAssignee->id,
                'assignee_name' => $previousAssignee->name,
            ],
            'new_values' => [
                'assignee_id' => null,
            ],
            'metadata' => [
                'customer_name' => $discovery->customer_name,
                'unassignment_type' => $unassigner ? 'manual' : 'self_unassignment',
            ]
        ]);
    }

    public static function logDiscoveryUpdate(Discovery $discovery, array $changes, ?User $user = null): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($changes as $field => $value) {
            if ($discovery->isDirty($field)) {
                $oldValues[$field] = $discovery->getOriginal($field);
                $newValues[$field] = $value;
            }
        }

        if (!empty($oldValues) || !empty($newValues)) {
            self::createLog([
                'user_id' => $user?->id ?? Auth::id(),
                'discovery_id' => $discovery->id,
                'action' => TransactionLog::ACTION_UPDATED,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => [
                    'customer_name' => $discovery->customer_name,
                    'updated_fields' => array_keys($changes),
                ]
            ]);
        }
    }

    public static function logDiscoveryDeleted(Discovery $discovery, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_DELETED,
            'old_values' => [
                'customer_name' => $discovery->customer_name,
                'status' => $discovery->status,
                'total_cost' => $discovery->total_cost,
            ],
            'metadata' => [
                'customer_email' => $discovery->customer_email,
                'deletion_timestamp' => now()->toISOString(),
            ]
        ]);
    }

    public static function logDiscoveryViewed(Discovery $discovery, ?User $user = null, string $viewType = 'internal'): void
    {
        // Only log customer views via shared link, not every internal view
        if ($viewType === 'shared_link') {
            self::createLog([
                'user_id' => $user?->id,
                'discovery_id' => $discovery->id,
                'action' => TransactionLog::ACTION_VIEWED,
                'performed_by_type' => $user ? TransactionLog::PERFORMER_USER : TransactionLog::PERFORMER_CUSTOMER,
                'performed_by_identifier' => $user ? null : $discovery->customer_email,
                'metadata' => [
                    'customer_name' => $discovery->customer_name,
                    'view_type' => $viewType,
                ]
            ]);
        }
    }
    public static function logDiscoveryShared(Discovery $discovery, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'discovery_id' => $discovery->id,
            'action' => TransactionLog::ACTION_SHARED,
            'new_values' => [
                'share_token' => $discovery->share_token,
                'share_url' => $discovery->share_url,
            ],
            'metadata' => [
                'customer_name' => $discovery->customer_name,
                'customer_email' => $discovery->customer_email,
            ]
        ]);
    }

    // =======================
    // ITEM LOGGING METHODS
    // =======================

    public static function logItemCreated(Item $item, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_ITEM,
            'entity_id' => $item->id,
            'action' => TransactionLog::ACTION_CREATED,
            'new_values' => [
                'item' => $item->item,
                'brand' => $item->brand,
                'price' => $item->price,
            ],
        ]);
    }

    public static function logItemUpdated(Item $item, array $changes, ?User $user = null): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($changes as $field => $value) {
            if ($item->isDirty($field)) {
                $oldValues[$field] = $item->getOriginal($field);
                $newValues[$field] = $value;
            }
        }

        if (!empty($oldValues) || !empty($newValues)) {
            self::createLog([
                'user_id' => $user?->id ?? Auth::id(),
                'entity_type' => TransactionLog::ENTITY_ITEM,
                'entity_id' => $item->id,
                'action' => TransactionLog::ACTION_UPDATED,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => [
                    'item_name' => $item->item,
                    'updated_fields' => array_keys($changes),
                ]
            ]);
        }
    }

    public static function logItemDeleted(Item $item, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_ITEM,
            'entity_id' => $item->id,
            'action' => TransactionLog::ACTION_DELETED,
            'old_values' => [
                'item' => $item->item,
                'brand' => $item->brand,
                'price' => $item->price,
            ],
            'metadata' => [
                'deletion_timestamp' => now()->toISOString(),
            ]
        ]);
    }

    public static function logItemPriceChanged(Item $item, $oldPrice, $newPrice, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_ITEM,
            'entity_id' => $item->id,
            'action' => TransactionLog::ACTION_PRICE_CHANGED,
            'old_values' => ['price' => $oldPrice],
            'new_values' => ['price' => $newPrice],
            'metadata' => [
                'item_name' => $item->item,
                'price_difference' => $newPrice - $oldPrice,
            ]
        ]);
    }

    public static function logItemAttachedToDiscovery(Item $item, Discovery $discovery, array $pivotData, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_ITEM,
            'entity_id' => $item->id,
            'action' => TransactionLog::ACTION_ATTACHED,
            'new_values' => $pivotData,
            'metadata' => [
                'item_name' => $item->item,
                'discovery_id' => $discovery->id,
                'customer_name' => $discovery->customer_name,
            ]
        ]);
    }

    public static function logItemDetachedFromDiscovery(Item $item, Discovery $discovery, array $pivotData, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_ITEM,
            'entity_id' => $item->id,
            'action' => TransactionLog::ACTION_DETACHED,
            'old_values' => $pivotData,
            'metadata' => [
                'item_name' => $item->item,
                'discovery_id' => $discovery->id,
                'customer_name' => $discovery->customer_name,
            ]
        ]);
    }

    // =======================
    // PROPERTY LOGGING METHODS
    // =======================

    public static function logPropertyCreated(Property $property, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_PROPERTY,
            'entity_id' => $property->id,
            'action' => TransactionLog::ACTION_CREATED,
            'new_values' => [
                'name' => $property->name,
                'city' => $property->city,
                'neighborhood' => $property->neighborhood,
                'is_active' => $property->is_active,
            ],
            'metadata' => [
                'company_id' => $property->company_id,
                'full_address' => $property->getFullAddressAttribute(),
            ]
        ]);
    }

    public static function logPropertyUpdated(Property $property, array $changes, ?User $user = null): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($changes as $field => $value) {
            if ($property->isDirty($field)) {
                $oldValues[$field] = $property->getOriginal($field);
                $newValues[$field] = $value;
            }
        }

        if (!empty($oldValues) || !empty($newValues)) {
            self::createLog([
                'user_id' => $user?->id ?? Auth::id(),
                'entity_type' => TransactionLog::ENTITY_PROPERTY,
                'entity_id' => $property->id,
                'action' => TransactionLog::ACTION_UPDATED,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => [
                    'property_name' => $property->name,
                    'company_id' => $property->company_id,
                    'updated_fields' => array_keys($changes),
                ]
            ]);
        }
    }

    public static function logPropertyDeleted(Property $property, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_PROPERTY,
            'entity_id' => $property->id,
            'action' => TransactionLog::ACTION_DELETED,
            'old_values' => [
                'name' => $property->name,
                'city' => $property->city,
                'neighborhood' => $property->neighborhood,
                'is_active' => $property->is_active,
            ],
            'metadata' => [
                'company_id' => $property->company_id,
                'deletion_timestamp' => now()->toISOString(),
            ]
        ]);
    }

    public static function logPropertyActivated(Property $property, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_PROPERTY,
            'entity_id' => $property->id,
            'action' => TransactionLog::ACTION_ACTIVATED,
            'old_values' => ['is_active' => false],
            'new_values' => ['is_active' => true],
            'metadata' => [
                'property_name' => $property->name,
                'company_id' => $property->company_id,
            ]
        ]);
    }

    public static function logPropertyDeactivated(Property $property, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_PROPERTY,
            'entity_id' => $property->id,
            'action' => TransactionLog::ACTION_DEACTIVATED,
            'old_values' => ['is_active' => true],
            'new_values' => ['is_active' => false],
            'metadata' => [
                'property_name' => $property->name,
                'company_id' => $property->company_id,
            ]
        ]);
    }

    /**
     * ========================================
     * WORKGROUP LOGGING METHODS
     * ========================================
     */

    public static function logWorkGroupCreated(WorkGroup $workGroup, array $data, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_WORKGROUP,
            'entity_id' => $workGroup->id,
            'action' => TransactionLog::ACTION_CREATED,
            'new_values' => $data,
            'metadata' => [
                'workgroup_name' => $workGroup->name,
                'company_id' => $workGroup->company_id,
            ]
        ]);
    }

    public static function logWorkGroupUpdated(WorkGroup $workGroup, array $changes, ?User $user = null): void
    {
        // Get original values for comparison
        $originalValues = [];
        foreach (array_keys($changes) as $field) {
            $originalValues[$field] = $workGroup->getOriginal($field);
        }

        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_WORKGROUP,
            'entity_id' => $workGroup->id,
            'action' => TransactionLog::ACTION_UPDATED,
            'old_values' => $originalValues,
            'new_values' => $changes,
            'metadata' => [
                'workgroup_name' => $workGroup->name,
                'company_id' => $workGroup->company_id,
            ]
        ]);
    }

    public static function logWorkGroupDeleted(WorkGroup $workGroup, ?User $user = null): void
    {
        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_WORKGROUP,
            'entity_id' => $workGroup->id,
            'action' => TransactionLog::ACTION_DELETED,
            'old_values' => $workGroup->toArray(),
            'metadata' => [
                'workgroup_name' => $workGroup->name,
                'company_id' => $workGroup->company_id,
            ]
        ]);
    }

    public static function logUserAssignedToWorkGroup(User $user, WorkGroup $workGroup, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_ASSIGNED,
            'new_values' => [
                'workgroup_id' => $workGroup->id,
                'workgroup_name' => $workGroup->name,
            ],
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'workgroup_id' => $workGroup->id,
                'workgroup_name' => $workGroup->name,
                'company_id' => $workGroup->company_id,
            ]
        ]);
    }

    public static function logUserRemovedFromWorkGroup(User $user, WorkGroup $workGroup, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_UNASSIGNED,
            'old_values' => [
                'workgroup_id' => $workGroup->id,
                'workgroup_name' => $workGroup->name,
            ],
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'workgroup_id' => $workGroup->id,
                'workgroup_name' => $workGroup->name,
                'company_id' => $workGroup->company_id,
            ]
        ]);
    }

    /**
     * ========================================
     * USER MANAGEMENT LOGGING METHODS
     * ========================================
     */

    public static function logUserCreated(User $user, array $data, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_CREATED,
            'new_values' => $data,
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_type' => $user->type,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    public static function logUserUpdated(User $user, array $changes, ?User $performer = null): void
    {
        // Get original values for comparison
        $originalValues = [];
        foreach (array_keys($changes) as $field) {
            $originalValues[$field] = $user->getOriginal($field);
        }

        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_UPDATED,
            'old_values' => $originalValues,
            'new_values' => $changes,
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_type' => $user->type,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    public static function logUserDeleted(User $user, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_DELETED,
            'old_values' => $user->toArray(),
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_type' => $user->type,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    public static function logUserPromoted(User $user, string $oldType, string $newType, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_PROMOTED,
            'old_values' => ['type' => $oldType],
            'new_values' => ['type' => $newType],
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'old_type' => $oldType,
                'new_type' => $newType,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    public static function logUserDemoted(User $user, string $oldType, string $newType, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $user->id,
            'action' => TransactionLog::ACTION_DEMOTED,
            'old_values' => ['type' => $oldType],
            'new_values' => ['type' => $newType],
            'metadata' => [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'old_type' => $oldType,
                'new_type' => $newType,
                'company_id' => $user->company_id,
            ]
        ]);
    }

    public static function logPrimaryAdminTransferred(User $oldAdmin, User $newAdmin, ?User $performer = null): void
    {
        self::createLog([
            'user_id' => $performer?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_USER,
            'entity_id' => $newAdmin->id,
            'action' => TransactionLog::ACTION_TRANSFERRED,
            'old_values' => [
                'old_admin_id' => $oldAdmin->id,
                'old_admin_name' => $oldAdmin->name,
            ],
            'new_values' => [
                'new_admin_id' => $newAdmin->id,
                'new_admin_name' => $newAdmin->name,
            ],
            'metadata' => [
                'action_type' => 'primary_admin_transfer',
                'old_admin_name' => $oldAdmin->name,
                'old_admin_email' => $oldAdmin->email,
                'new_admin_name' => $newAdmin->name,
                'new_admin_email' => $newAdmin->email,
                'company_id' => $newAdmin->company_id,
            ]
        ]);
    }

    /**
     * ========================================
     * COMPANY LOGGING METHODS
     * ========================================
     */

    public static function logCompanyUpdated(Company $company, array $changes, ?User $user = null): void
    {
        // Get original values for comparison
        $originalValues = [];
        foreach (array_keys($changes) as $field) {
            $originalValues[$field] = $company->getOriginal($field);
        }

        self::createLog([
            'user_id' => $user?->id ?? Auth::id(),
            'entity_type' => TransactionLog::ENTITY_COMPANY,
            'entity_id' => $company->id,
            'action' => TransactionLog::ACTION_UPDATED,
            'old_values' => $originalValues,
            'new_values' => $changes,
            'metadata' => [
                'company_name' => $company->name,
                'tax_number' => $company->tax_number,
            ]
        ]);
    }

    // =======================
    // LOG CLEANUP METHODS
    // =======================

    public static function deleteOldLogs(int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return TransactionLog::where('created_at', '<', $cutoffDate)->delete();
    }

    public static function deleteLogsByEntity(string $entityType, int $entityId): int
    {
        return TransactionLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }

    public static function deleteLogsByUser(int $userId): int
    {
        return TransactionLog::where('user_id', $userId)->delete();
    }

    public static function deleteLogsByAction(string $action, int $daysToKeep = 7): int
    {
        $cutoffDate = now()->subDays($daysToKeep);

        return TransactionLog::where('action', $action)
            ->where('created_at', '<', $cutoffDate)
            ->delete();
    }    private static function createLog(array $data): void
    {
        // Set default values
        $data['entity_type'] = $data['entity_type'] ?? TransactionLog::ENTITY_DISCOVERY;
        $data['entity_id'] = $data['entity_id'] ?? $data['discovery_id'] ?? null;
        $data['performed_by_type'] = $data['performed_by_type'] ?? TransactionLog::PERFORMER_USER;

        TransactionLog::create($data);
    }
}
