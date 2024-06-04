<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function users(Request $request)
    {
        $userId = $request->user()->id;
        $users = DB::table('messages')
            ->select('users.id', 'users.name')
            ->join('users', function ($join) use ($userId) {
                $join->on('users.id', '=', 'messages.sender_id')
                     ->orWhere('users.id', '=', 'messages.receiver_id')
                     ->where('users.id', '!=', $userId);
            })
            ->where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->groupBy('users.id', 'users.name')
            ->orderBy('messages.created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'success',
            'users' => $users,
        ]);
    }

    public function storeMessage(Request $request){
        $message = Message::create([
                'message_id' => Str::uuid(),
                'chat_id' => $request->chat_id,
                'sender_id' => $request->user()->id,
                'receiver_id' => $request->receiver_id,
                'message' => $request->message,
            ]);


        return response()->json([
            'message' => 'success',
            'data' => $message,
        ]);
    }

    public function getMessages(Request $request){
        // $messages = Message::where('receiver_id', [$request->user_id, $request->user()->id])
        //     ->orWhere('sender_id', [$request->user_id, $request->user()->id])
        //     // ->orderBy('created_at', 'desc')
        //     ->get();

        $userId = $request->user()->id;
        $otherUserId = $request->user_id;

        $messages = Message::where(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $userId)->where('receiver_id', $otherUserId);
        })->orWhere(function ($query) use ($userId, $otherUserId) {
            $query->where('sender_id', $otherUserId)->where('receiver_id', $userId);
        })->get();

        return response()->json([
            'message' => 'success',
            'data' => $messages,
        ]);
    }
}
