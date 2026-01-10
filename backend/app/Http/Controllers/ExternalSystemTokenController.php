<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class ExternalSystemTokenController extends Controller
{
    /**
     * Get all external system tokens for the guarantor
     * GET /guarantor/external-system-tokens
     */
    public function index(Request $request)
    {
        // Verify user is guarantor
        $user = $request->user();
        
        if (!$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can manage external tokens.',
            ], 403);
        }

        // Get all external system tokens for this user
        $tokens = PersonalAccessToken::where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->where('name', 'external_system')
            ->latest()
            ->get()
            ->map(function ($token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at,
                    'expires_at' => $token->expires_at,
                    'created_at' => $token->created_at,
                    'is_active' => $token->expires_at ? now()->lt($token->expires_at) : true,
                ];
            });

        return response()->json([
            'tokens' => $tokens,
            'total' => $tokens->count(),
        ], 200);
    }

    /**
     * Create new external system token
     * POST /guarantor/external-system-tokens
     */
    public function store(Request $request)
    {
        // Verify user is guarantor
        $user = $request->user();
        
        if (!$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can create external tokens.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string|max:255',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string|in:internship:defend',
            'expires_days' => 'nullable|integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create token with abilities
        $expiresAt = $request->expires_days 
            ? now()->addDays($request->expires_days) 
            : null;

        $token = $user->createToken(
            'external_system',
            $request->abilities,
            $expiresAt
        );

        return response()->json([
            'message' => 'External system token created successfully.',
            'token' => $token->plainTextToken, // This is shown ONLY ONCE
            'token_info' => [
                'id' => $token->accessToken->id,
                'abilities' => $request->abilities,
                'expires_at' => $expiresAt,
                'created_at' => $token->accessToken->created_at,
            ],
        ], 201);
    }

    /**
     * Revoke (delete) external system token
     * DELETE /guarantor/external-system-tokens/{id}
     */
    public function destroy(Request $request, $id)
    {
        // Verify user is guarantor
        $user = $request->user();
        
        if (!$user->hasRole('guarantor')) {
            return response()->json([
                'message' => 'Unauthorized. Only guarantors can revoke external tokens.',
            ], 403);
        }

        // Find token
        $token = PersonalAccessToken::where('id', $id)
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->where('name', 'external_system')
            ->first();

        if (!$token) {
            return response()->json([
                'message' => 'Token not found.',
            ], 404);
        }

        // Delete token
        $token->delete();

        return response()->json([
            'message' => 'External system token has been revoked successfully.',
        ], 200);
    }
}