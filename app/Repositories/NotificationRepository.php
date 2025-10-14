<?php

namespace App\Repositories;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Notification Repository
 * 
 * Handles all data access operations for notifications.
 * Provides specialized methods for notification management and tracking.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class NotificationRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DatabaseNotification();
    }

    /**
     * Get notifications for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type (e.g., App\Models\Drivers)
     * @param int $notifiableId Notifiable ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getForNotifiable(string $notifiableType, int $notifiableId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            [
                'notifiable_type' => $notifiableType,
                'notifiable_id' => $notifiableId
            ],
            ['created_at' => 'desc'],
            $perPage
        );
    }

    /**
     * Get unread notifications for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type
     * @param int $notifiableId Notifiable ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getUnreadForNotifiable(string $notifiableType, int $notifiableId, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get read notifications for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type
     * @param int $notifiableId Notifiable ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getReadForNotifiable(string $notifiableType, int $notifiableId, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNotNull('read_at')
            ->orderBy('read_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get notifications by type.
     *
     * @param string $type Notification type
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['type' => $type],
            ['created_at' => 'desc'],
            $perPage
        );
    }

    /**
     * Mark notification as read.
     *
     * @param string $notificationId Notification ID
     * @return Model
     */
    public function markAsRead(string $notificationId): Model
    {
        return $this->update($notificationId, ['read_at' => now()]);
    }

    /**
     * Mark multiple notifications as read.
     *
     * @param array $notificationIds Array of notification IDs
     * @return int Number of updated records
     */
    public function markManyAsRead(array $notificationIds): int
    {
        return $this->newQuery()
            ->whereIn('id', $notificationIds)
            ->update(['read_at' => now()]);
    }

    /**
     * Mark all notifications as read for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type
     * @param int $notifiableId Notifiable ID
     * @return int Number of updated records
     */
    public function markAllAsReadForNotifiable(string $notifiableType, int $notifiableId): int
    {
        return $this->newQuery()
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type
     * @param int $notifiableId Notifiable ID
     * @return int
     */
    public function getUnreadCount(string $notifiableType, int $notifiableId): int
    {
        return $this->count([
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
        ]) - $this->newQuery()
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->whereNotNull('read_at')
            ->count();
    }

    /**
     * Get recent notifications.
     *
     * @param int $hours Hours threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRecent(int $hours = 24, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }

    /**
     * Get notifications created within date range.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int|null $perPage Number of records per page
     * @return Collection|LengthAwarePaginator
     */
    public function getCreatedBetween(string $from, string $to, ?int $perPage = null)
    {
        $query = $this->newQuery()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->orderBy('created_at', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get notification statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'unread' => (clone $baseQuery)->whereNull('read_at')->count(),
            'read' => (clone $baseQuery)->whereNotNull('read_at')->count(),
            'sent_today' => (clone $baseQuery)->whereDate('created_at', today())->count(),
            'sent_this_week' => (clone $baseQuery)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'sent_this_month' => (clone $baseQuery)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    /**
     * Get notification statistics by type.
     *
     * @return array
     */
    public function getStatisticsByType(): array
    {
        return $this->newQuery()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Delete old notifications.
     *
     * @param int $days Days threshold
     * @return int Number of deleted notifications
     */
    public function deleteOlderThan(int $days): int
    {
        return $this->newQuery()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Delete read notifications older than specified days.
     *
     * @param int $days Days threshold
     * @return int Number of deleted notifications
     */
    public function deleteReadOlderThan(int $days): int
    {
        return $this->newQuery()
            ->whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Delete notifications for a notifiable entity.
     *
     * @param string $notifiableType Notifiable type
     * @param int $notifiableId Notifiable ID
     * @return int Number of deleted notifications
     */
    public function deleteForNotifiable(string $notifiableType, int $notifiableId): int
    {
        return $this->deleteWhere([
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId
        ]);
    }

    /**
     * Get average read time.
     *
     * @return float Average time in minutes
     */
    public function getAverageReadTime(): float
    {
        $avgMinutes = $this->newQuery()
            ->whereNotNull('read_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, read_at)) as avg_minutes')
            ->first()
            ->avg_minutes ?? 0;

        return round($avgMinutes, 2);
    }

    /**
     * Get read rate percentage.
     *
     * @return float Read rate percentage
     */
    public function getReadRate(): float
    {
        $total = $this->count();
        
        if ($total === 0) {
            return 0;
        }

        $read = $this->newQuery()->whereNotNull('read_at')->count();

        return round(($read / $total) * 100, 2);
    }

    /**
     * Get most active notification types.
     *
     * @param int $limit Number of types to return
     * @return array
     */
    public function getMostActiveTypes(int $limit = 10): array
    {
        return $this->newQuery()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->orderBy('count', 'desc')
            ->limit($limit)
            ->pluck('count', 'type')
            ->toArray();
    }

    /**
     * Search notifications with filters.
     *
     * @param array $filters Search filters
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchNotifications(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery();

        // Apply notifiable type filter
        if (!empty($filters['notifiable_type'])) {
            $query->where('notifiable_type', $filters['notifiable_type']);
        }

        // Apply notifiable ID filter
        if (!empty($filters['notifiable_id'])) {
            $query->where('notifiable_id', $filters['notifiable_id']);
        }

        // Apply type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Apply read status filter
        if (isset($filters['is_read'])) {
            if ($filters['is_read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        // Apply date range filters
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }
        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }
}
