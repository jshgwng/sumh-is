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
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            // check if the user exists
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // check if the user has verified their email
                if (!$user->email_verified_at) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email not verified'
                    ], 400);
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
                    'message' => 'User not found'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
