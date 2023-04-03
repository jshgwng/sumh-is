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

    public function getUsers(Request $request)
    {
        $user = $request->user();
        abort_if(Gate::denies('get user', $user), 403, 'You are not authorized to view this page');

        $users = User::all();

        // format the users
        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id' => count($data) + 1,
                'user_id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'phone' => $user->phone,
                'username' => $user->username,
                'email' => $user->email,
                'roles' => $user->roles,
                'status' => $user->deleted_at ? 'inactive' : 'active',
            ];
        }

        return response()->json([
            'status' => true,
            'users' => $data
        ]);
    }

    public function showUser(Request $request, $id)
    {
        $user = $request->user();

        abort_if(Gate::denies('get user', $user), 403, 'You are not authorized to view this page');

        $data = User::find($id);
        
        return response()->json([
            'status' => true,
            'user' => $data,
        ]);
    }

    public function userRoles(Request $request, $id){
        $user = $request->user();

        abort_if(Gate::denies('get user', $user), 403, 'You are not authorized to view this page');

        $data = User::find($id);

        $all_roles = DB::table('roles')->get();

        // combine the roles; if the user has a role, mark it as checked
        $roles = [];
        foreach ($all_roles as $role) {
            $checked = false;
            foreach ($data->roles as $user_role) {
                if($user_role->id == $role->id){
                    $checked = true;
                }
            }

            $roles[] = [
                'id' => $role->id,
                'name' => $role->name,
                'checked' => $checked
            ];
        }
        return response()->json([
            'status' => true,
            // 'roles' => $data->roles,
            // 'all_roles' => $all_roles,
            'roles' => $roles
        ]);
    }

    public function updateUserRoles(Request $request){
        $admin = $request->user();

        abort_if(Gate::denies('get user', $admin), 403, 'You are not authorized to view this page');

        $user = User::find($request->user_id);

        $user->roles()->sync($request->roles);

        return response()->json([
            'status' => true,
            'message' => 'User roles updated'
        ]);
    }
}