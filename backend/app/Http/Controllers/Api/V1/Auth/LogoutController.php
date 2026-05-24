<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LogoutUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private readonly LogoutUserService $logoutUser,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->logoutUser->logout($request);

        return response()->json(['message' => 'Logged out']);
    }
}
