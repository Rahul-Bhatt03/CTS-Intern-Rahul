<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(private UserService $userService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
      try{
        $result=$this->userService->register($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => $result['data']
        ], 201);
      }catch(\Exception $e){
            return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $e->getCode() ?: 500);
      }
    }

    public function login(LoginRequest $request): JsonResponse
    {
      try{
        $result=$this->userService->login($request->validated());
        return response()->json([
            'success' => true,
            'message' => 'User logged in successfully',
            'data' => $result['data']
        ], 201);
      }catch(\Exception $e){
           return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $e->getCode() ?: 401);
      }
    }

    public function logout():JsonResponse{
        auth()->user()->tokens()-delete();
        return response()->json(['message' => 'Successfully logged out'], 200);
    }
}
