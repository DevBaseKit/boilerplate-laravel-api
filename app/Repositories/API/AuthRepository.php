<?php

namespace App\Repositories\API;

use App\Contracts\Repositories\API\AuthRepositoryInterface;
use App\Models\User;

class AuthRepository implements AuthRepositoryInterface
{
    /**
     * Create user.
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Find user by id or fail.
     */
    public function findOrFail(mixed $id): User
    {
        return User::findOrFail($id);
    }

    /**
     * Find user by id.
     */
    public function find(mixed $id): ?User
    {
        return User::find($id);
    }

    /**
     * Update user model instance.
     */
    public function updateModel(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    /**
     * Delete user model instance.
     */
    public function deleteModel(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
