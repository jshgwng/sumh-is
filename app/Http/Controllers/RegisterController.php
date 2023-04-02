<?php

namespace App\Http\Controllers;

use DB;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Mail\VerificationCodeEmail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    // function to register the user and email the verification token (8 alphanumeric characters)
    public function register(Request $request)
    {
        try {
            $user = $request->user();
            // validate the request
            $request->validate([
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|string|email|unique:users',
                'username' => 'required|string|unique:users',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|confirmed|min:8',
            ]);

            // Create the user
            $user = User::create([
                'id' => Str::uuid(),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
            ]);

            $user->assignRole('respondent');
            // Generate the 8 alphanumeric characters token
            $token = Str::random(8);

            // Insert the token into the database
            DB::table('email_verifications')->insert([
                'user_id' => $user->id,
                'token' => $token,
                'email' => $user->email,
                'expires_at' => now()->addMinutes(30),
            ]);

            // Send the email verification token to the user
            Mail::to($user->email)->send(new VerificationCodeEmail($token, $user->first_name . ' ' . $user->last_name));

            // return the created user
            return response()->json(
                [
                    'status' => true,
                    'message' => 'User registered successfully',
                    'user' => $user
                ],
                201
            );

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage(),
            ], 500);
        }
    }
}
