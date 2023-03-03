<?php

namespace App\Http\Controllers;

use App\Mail\VerifiedEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VerifyController extends Controller
{
    // function to verify the user email
    public function verify(Request $request)
    {
        try {
            // validate the request
            $request->validate([
                'email' => 'required|string|email',
                'token' => 'required|string',
            ]);

            // check if the user exists
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // check if the user has verified their email
                if ($user->email_verified_at) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Email already verified'
                    ], 400);
                }

                // check if the token is valid
                $token = DB::table('email_verifications')->where('email', $request->email)->value('token');

                if ($token == $request->token) {
                    // update the user email_verified_at field
                    DB::table('users')->where('email', $request->email)->update(['email_verified_at' => now()]);
                    $user->email_verified_at = now();
                    $user->save();

                    Mail::to($user->email)->send(new VerifiedEmail($user->first_name . ' ' . $user->last_name));
                    return response()->json([
                        'status' => true,
                        'message' => 'Email verified successfully'
                    ], 200);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Invalid verification token'
                    ], 400);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found'
                ], 404);
            }

        } catch (\Throwable $th) {
            throw $th;
        }
    }
}