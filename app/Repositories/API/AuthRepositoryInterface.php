<?php

namespace App\Repositories\API;

use App\Models\User;

interface AuthRepositoryInterface
{
    /**
     * Create user.
     */
    public function create(array $data): User;

    /**
     * Find user by id or fail.
     */
    public function findOrFail(mixed $id): User;

    /**
     * Find user by id.
     */
    public function find(mixed $id): ?User;

    /**
     * Update user model instance.
     */
    public function updateModel(User $user, array $data): User;

    /**
     * Delete user model instance.
     */
    public function deleteModel(User $user): bool;

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User;
}
