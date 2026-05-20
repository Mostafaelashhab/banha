<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Support\AnonSeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone'    => ['required', 'string'],
            'password' => ['required', 'string'],
            'device'   => ['nullable', 'string', 'max:120'],
        ]);

        $user = User::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'phone' => ['الرقم أو كلمة السر مش مظبوطة'],
            ]);
        }

        if ($user->is_banned) {
            return response()->json(['message' => 'الحساب موقوف'], 403);
        }

        $token = $user->createToken($data['device'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user->load('zone')),
        ]);
    }

    public function signup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:60'],
            'phone'                 => ['required', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'device'                => ['nullable', 'string', 'max:120'],
        ]);

        $username = self::deriveUsername($data['name'], $data['phone']);

        $user = User::create([
            'username'    => $username,
            'phone'       => $data['phone'],
            'password'    => $data['password'],
            'persona'     => 'resident',
            'avatar_seed' => AnonSeed::generate(),
            'reputation'  => 50,
        ]);

        $token = $user->createToken($data['device'] ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => new UserResource($user),
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('zone')),
        ]);
    }

    private static function deriveUsername(string $name, string $phone): string
    {
        $base = preg_replace('/\s+/', '_', mb_strtolower(trim($name))) ?: 'user';
        $candidate = $base;
        $i = 0;
        while (User::where('username', $candidate)->exists()) {
            $candidate = $base . '_' . substr($phone, -4) . ($i ? $i : '');
            $i++;
            if ($i > 5) {
                $candidate = $base . '_' . bin2hex(random_bytes(2));
                break;
            }
        }
        return $candidate;
    }
}
