<?php

namespace App\Services\API;

interface AuthServiceInterface
{
    /**
     * Register a new user and login.
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array;

    /**
     * Authenticate user credentials and return token details.
     *
     * @param array $credentials
     * @return array|null
     */
    public function login(array $credentials): ?array;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Get authenticated user profile.
     *
     * @return array|null
     */
    public function me(): ?array;

    /**
     * Refresh the JWT token.
     *
     * @return array
     */
    public function refresh(): array;
}
