<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getUser(Request $request, $id)
    {
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
    }
}
