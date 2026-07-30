<?php

namespace App\Services\registartion;

use App\Models\User;
use App\Models\Driver;
use App\Models\Provider_Product; // Import your ProviderProduct model
use App\Models\Provider_Service; // Import your ProviderService model
use Illuminate\Support\Facades\Hash;
class login
{
    /**
     * Register a new user and create related records based on user type.
     *
     * @param array $data
     * @return User
     */
    public function login(array $data)
    {
        // البحث عن المستخدم باستخدام البريد أو الهاتف
        $user = User::when(isset($data['email']), function($query) use ($data) {
                return $query->where('email', $data['email']);
            })
            ->when(isset($data['phone']), function($query) use ($data) {
                return $query->where('phone', $data['phone']);
            })
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return [
                'status' => 401,
                'response' => response()->json([
                    'status' => 'error',
                    'message' => 'Invalid credentials'
                ], 401)
            ];
        }

        // إنشاء token مع تحديد النطاق
        $token = $user->createToken('auth-token', ['*'])->plainTextToken;

        return [
            'status' => 200,
            'token' => $token,
            'user' => $user,
            'token_type' => 'Bearer'
        ];
    }


}
