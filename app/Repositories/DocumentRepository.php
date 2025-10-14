<?php

namespace App\Repositories;

use App\Models\DriverDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Document Repository
 * 
 * Handles all data access operations for the DriverDocument model.
 * Provides specialized methods for document management and verification.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class DocumentRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new DriverDocument();
    }

    /**
     * Get documents by driver ID.
     *
     * @param int $driverId Driver ID
     * @param array $columns Columns to select
     * @return Collection
     */
    public function getByDriverId(int $driverId, array $columns = ['*']): Collection
    {
        return $this->findBy('driver_id', $driverId, $columns);
    }

    /**
     * Get documents by type.
     *
     * @param string $type Document type
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByType(string $type, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['document_type' => $type],
            ['created_at' => 'desc'],
            $perPage
        );
    }

    /**
     * Get documents by verification status.
     *
     * @param string $status Verification status
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByVerificationStatus(string $status, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['verification_status' => $status],
            ['created_at' => 'desc'],
            $perPage,
            ['driver']
        );
    }

    /**
     * Get pending verification documents.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getPendingVerification(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('pending', $perPage);
    }

    /**
     * Get verified documents.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getVerified(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('verified', $perPage);
    }

    /**
     * Get rejected documents.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getRejected(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getByVerificationStatus('rejected', $perPage);
    }

    /**
     * Find document by driver and type.
     *
     * @param int $driverId Driver ID
     * @param string $type Document type
     * @return Model|null
     */
    public function findByDriverAndType(int $driverId, string $type): ?Model
    {
        return $this->findOneWhere([
            'driver_id' => $driverId,
            'document_type' => $type
        ]);
    }

    /**
     * Update document verification status.
     *
     * @param int $documentId Document ID
     * @param string $status Verification status
     * @param int $verifiedBy Admin user ID
     * @param string|null $notes Verification notes
     * @return Model
     */
    public function updateVerificationStatus(int $documentId, string $status, int $verifiedBy, ?string $notes = null): Model
    {
        $data = [
            'verification_status' => $status,
            'verified_by' => $verifiedBy,
            'verification_notes' => $notes,
        ];

        if ($status === 'verified') {
            $data['verified_at'] = now();
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = now();
        }

        return $this->update($documentId, $data);
    }

    /**
     * Get documents uploaded within date range.
     *
     * @param string $from Start date
     * @param string $to End date
     * @param int|null $perPage Number of records per page
     * @return Collection|LengthAwarePaginator
     */
    public function getUploadedBetween(string $from, string $to, ?int $perPage = null)
    {
        $query = $this->newQuery()
            ->whereDate('uploaded_at', '>=', $from)
            ->whereDate('uploaded_at', '<=', $to)
            ->with('driver')
            ->orderBy('uploaded_at', 'desc');

        return $perPage ? $query->paginate($perPage) : $query->get();
    }

    /**
     * Get document statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'pending' => (clone $baseQuery)->where('verification_status', 'pending')->count(),
            'verified' => (clone $baseQuery)->where('verification_status', 'verified')->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', 'rejected')->count(),
            'uploaded_today' => (clone $baseQuery)->whereDate('uploaded_at', today())->count(),
            'uploaded_this_week' => (clone $baseQuery)->whereBetween('uploaded_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'uploaded_this_month' => (clone $baseQuery)->whereMonth('uploaded_at', now()->month)->whereYear('uploaded_at', now()->year)->count(),
        ];
    }

    /**
     * Get documents by driver with specific types.
     *
     * @param int $driverId Driver ID
     * @param array $types Document types
     * @return Collection
     */
    public function getByDriverAndTypes(int $driverId, array $types): Collection
    {
        return $this->findWhere([
            'driver_id' => $driverId,
            'document_type' => $types
        ]);
    }

    /**
     * Check if driver has document of type.
     *
     * @param int $driverId Driver ID
     * @param string $type Document type
     * @return bool
     */
    public function driverHasDocument(int $driverId, string $type): bool
    {
        return $this->exists([
            'driver_id' => $driverId,
            'document_type' => $type
        ]);
    }

    /**
     * Delete documents by driver ID.
     *
     * @param int $driverId Driver ID
     * @return int Number of deleted documents
     */
    public function deleteByDriverId(int $driverId): int
    {
        return $this->deleteWhere(['driver_id' => $driverId]);
    }

    /**
     * Get expired documents.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getExpired(int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->with('driver')
            ->orderBy('expiry_date', 'asc');

        return $query->paginate($perPage);
    }

    /**
     * Get documents expiring soon.
     *
     * @param int $days Number of days threshold
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getExpiringSoon(int $days = 30, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->with('driver')
            ->orderBy('expiry_date', 'asc');

        return $query->paginate($perPage);
    }

    /**
     * Bulk update verification status.
     *
     * @param array $documentIds Array of document IDs
     * @param string $status Verification status
     * @param int $verifiedBy Admin user ID
     * @return int Number of updated records
     */
    public function bulkUpdateVerificationStatus(array $documentIds, string $status, int $verifiedBy): int
    {
        $data = [
            'verification_status' => $status,
            'verified_by' => $verifiedBy,
            'updated_at' => now(),
        ];

        if ($status === 'verified') {
            $data['verified_at'] = now();
        } elseif ($status === 'rejected') {
            $data['rejected_at'] = now();
        }

        return $this->newQuery()
            ->whereIn('id', $documentIds)
            ->update($data);
    }
}
