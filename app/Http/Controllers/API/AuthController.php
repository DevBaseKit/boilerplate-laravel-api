<?php

namespace App\Http\Controllers\API;

use App\Constants\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\LoginRequest;
use App\Http\Requests\API\RegisterRequest;
use App\Services\API\AuthServiceInterface;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * @var AuthServiceInterface
     */
    protected AuthServiceInterface $authService;

    /**
     * AuthController constructor.
     *
     * @param AuthServiceInterface $authService
     */
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     *
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());
        return $this->sendSuccess($result, 'User registered successfully', ApiStatusCode::CREATED);
    }

    /**
     * Authenticate and login a user.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());
        if (!$result) {
            return $this->sendError('Unauthorized', ['Invalid email or password'], ApiStatusCode::UNAUTHORIZED);
        }
        return $this->sendSuccess($result, 'Login successful');
    }

    /**
     * Logout user (invalidate token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return $this->sendSuccess(null, 'Successfully logged out');
    }

    /**
     * Get authenticated user profile.
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        $result = $this->authService->me();
        return $this->sendSuccess($result, 'User profile fetched successfully');
    }

    /**
     * Refresh authenticated user JWT token.
     *
     * @return JsonResponse
     */
    public function refresh(): JsonResponse
    {
        $result = $this->authService->refresh();
        return $this->sendSuccess($result, 'Token refreshed successfully');
    }
}
