<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Mail\VerificationCodeEmail;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function getUser(Request $request, $id)
    {
        try {
            $user = $request->user();

            // abort_if(Gate::denies('get user', $user), 403, 'You are not authorized to view this page');
            $user = User::find($id);

            if ($user) {
                return response()->json([
                    'status' => true,
                    'message' => 'User found',
                    'user' => $user
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'user' => null
                ]);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $th->getMessage()
            ]);
        }
    }

    public function newVerificationToken(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        $token = Str::random(8);

        $email_verification = DB::table('email_verifications')->where('email', $request->email)->update([
            'token' => $token,
            'expires_at' => now()->addMinutes(30),
            'updated_at' => now()
        ]);

        Mail::to($user->email)->send(new VerificationCodeEmail($token, $user->first_name . ' ' . $user->last_name));

        return response()->json([
            'status' => true,
            'message' => 'Verification token sent'
        ]);
    }

    public function getUserRole(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'role' => $user->role
        ]);
    }
}