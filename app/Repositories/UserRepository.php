<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Get user by email
     */
    public function getByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Get user by email or fail
     */
    public function getByEmailOrFail(string $email): User
    {
        return $this->model->where('email', $email)->firstOrFail();
    }

    /**
     * Get users with their tokens
     */
    public function getWithTokens(): Collection
    {
        return $this->model->with('tokens')->get();
    }

    /**
     * Get users created in date range
     */
    public function getByDateRange(string $from, string $to): Collection
    {
        return $this->model
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->get();
    }

    /**
     * Get verified users
     */
    public function getVerified(): Collection
    {
        return $this->model->whereNotNull('email_verified_at')->get();
    }

    /**
     * Get unverified users
     */
    public function getUnverified(): Collection
    {
        return $this->model->whereNull('email_verified_at')->get();
    }

    /**
     * Check if email exists
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->model->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Create user with hashed password
     */
    public function createWithHashedPassword(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        return $this->create($data);
    }

    /**
     * Update user password
     */
    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, ['password' => bcrypt($password)]);
    }

    /**
     * Mark email as verified
     */
    public function markEmailAsVerified(int $id): bool
    {
        return $this->update($id, ['email_verified_at' => now()]);
    }

    /**
     * Mark email as unverified
     */
    public function markEmailAsUnverified(int $id): bool
    {
        return $this->update($id, ['email_verified_at' => null]);
    }
}
