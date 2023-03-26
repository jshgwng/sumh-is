<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // function to login users with verified email
    public function login(Request $request)
    {
        try {
            // validate the request
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            // check if the user exists
            $user = User::where('username', $request->username)->first();

            if ($user) {
                // check if the user has verified their email
                if (!$user->email_verified_at) {
                    return response()->json([
                        'status' => true,
                        'message' => 'Email not verified',
                        'is_verified' => false,
                        'user' => $user,
                    ], 200);
                }

                // check if the password is correct
                if (Hash::check($request->password, $user->password)) {
                    // generate the access token
                    $accessToken = $user->createToken('authToken')->plainTextToken;

                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'user' => $user,
                        'access_token' => $accessToken
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid credentials'
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'The user does not exist'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
