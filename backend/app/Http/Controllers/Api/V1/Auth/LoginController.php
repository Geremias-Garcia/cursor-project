<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\LoginUserService;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(
        private readonly LoginUserService $loginUser,
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = $this->loginUser->login(
            $request,
            $request->only('email', 'password'),
            $request->boolean('remember'),
        );

        return (new UserResource($user))->response();
    }
}
