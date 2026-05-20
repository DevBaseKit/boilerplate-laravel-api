<?php

namespace App\Services\API;

use App\Contracts\Repositories\API\AuthRepositoryInterface;
use App\Contracts\Services\API\AuthServiceInterface;
use App\Http\Resources\UserResource;
use App\Services\AuditTrailService;
use Illuminate\Support\Facades\Auth;

class AuthService implements AuthServiceInterface
{
    /**
     * @var AuthRepositoryInterface
     */
    protected AuthRepositoryInterface $authRepository;
    protected AuditTrailService $auditTrailService;

    /**
     * AuthService constructor.
     *
     * @param AuthRepositoryInterface $authRepository
     * @param AuditTrailService $auditTrailService
     */
    public function __construct(AuthRepositoryInterface $authRepository, AuditTrailService $auditTrailService)
    {
        $this->authRepository = $authRepository;
        $this->auditTrailService = $auditTrailService;
    }

    /**
     * Register a new user and login.
     */
    public function register(array $data): array
    {
        $user = $this->authRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // Password casting is automatically hashed in modern Laravel models
        ]);

        $token = Auth::guard('api')->login($user);
        $this->auditTrailService->record('auth.register', $user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Authenticate user credentials and return token details.
     */
    public function login(array $credentials): ?array
    {
        if (!$token = Auth::guard('api')->attempt($credentials)) {
            $this->auditTrailService->record('auth.login_failed', null, [
                'email' => $credentials['email'] ?? null,
            ]);
            return null;
        }

        $user = Auth::guard('api')->user();
        $this->auditTrailService->record('auth.login', $user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): void
    {
        $user = Auth::guard('api')->user();
        $this->auditTrailService->record('auth.logout', $user);
        Auth::guard('api')->logout();
    }

    /**
     * Get authenticated user profile.
     */
    public function me(): ?array
    {
        $user = Auth::guard('api')->user();
        return $user ? UserResource::make($user)->resolve() : null;
    }

    /**
     * Refresh the JWT token.
     */
    public function refresh(): array
    {
        $token = Auth::guard('api')->refresh();
        $user = Auth::guard('api')->user();
        $this->auditTrailService->record('auth.refresh', $user);
        return $this->respondWithToken($token, $user);
    }

    /**
     * Structure the token response.
     *
     * @param string $token
     * @param mixed $user
     * @return array
     */
    protected function respondWithToken(string $token, mixed $user): array
    {
        $refreshTtl = (int) config('jwt.refresh_ttl', 20160);
        $refreshToken = $this->generateRefreshToken($user, $refreshTtl);

        return [
            'access_token' => $token,
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60,
            'refresh_token' => $refreshToken,
            'refresh_expires_in' => $refreshTtl * 60,
            'user' => $user ? UserResource::make($user)->resolve() : null,
        ];
    }

    /**
     * Generate a dedicated refresh token for client-side refresh flow.
     */
    protected function generateRefreshToken(mixed $user, int $refreshTtl): ?string
    {
        if (!$user) {
            return null;
        }

        $guard = Auth::guard('api');
        $defaultTtl = (int) config('jwt.ttl', 60);

        $refreshToken = $guard
            ->setTTL($refreshTtl)
            ->claims(['token_use' => 'refresh'])
            ->fromUser($user);

        // Reset guard TTL so next access token generation keeps default lifetime.
        $guard->setTTL($defaultTtl);

        return $refreshToken;
    }
}
