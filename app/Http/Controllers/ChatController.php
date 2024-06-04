<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function store(Request $request)
    {
        // check if chat already exists
        $chat = Chat::where('sender_id', $request->user()->id)
            ->where('receiver_id', $request->receiver_id)
            ->orWhere('sender_id', $request->receiver_id)
            ->where('receiver_id', $request->user()->id)
            ->first();

        if ($chat) {
            return response()->json([
                'message' => 'success',
                'chat' => $chat,
            ]);
        }

        $chat = Chat::create([
            'chat_id' => Str::uuid(),
            'sender_id' => $request->user()->id,
            'receiver_id' => $request->receiver_id,
        ]);

        $chat = DB::table('chats')
            ->join('users', 'users.id', '=', 'chats.receiver_id')
            ->select('chats.*', 'users.first_name', 'users.last_name')
            ->where('chats.chat_id', $chat->chat_id)
            ->first();

        return response()->json([
            'message' => 'success',
            'chat' => $chat,
        ]);
    }

    public function fetchChat(Request $request)
    {
        $messages = DB::table('messages')
            ->join('chats', 'chats.chat_id', '=', 'messages.chat_id')
            ->join('users', 'users.id', '=', 'chats.receiver_id')
            ->select('messages.*', 'users.first_name', 'users.last_name')
            ->where('messages.chat_id', $request->chat_id)
            ->where('chats.sender_id', $request->user()->id)
            ->orWhere('chats.receiver_id', $request->user()->id)
            ->orderBy('messages.created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'success',
            'chats' => $messages,
            $request->all()
        ]);
    }

    public function fetchChats(Request $request)
    {
        // $chats = DB::table('chats')
        //     ->join('users', 'users.id', '=', 'chats.receiver_id')
        //     ->select('chats.chat_id', 'users.first_name', 'users.last_name')
        //     ->where('chats.sender_id', $request->user()->id)
        //     ->orWhere('chats.receiver_id', $request->user()->id)
        //     ->orderBy('chats.created_at', 'desc')
        //     ->get();

        // $chats = Chat::
        //     join('users as receiver', 'receiver.id', '=', 'chats.receiver_id')
        //     ->join('users as sender', 'sender.id', '=', 'chats.sender_id')
        //     ->select('chats.chat_id', 'receiver.first_name as receiver_first_name', 'receiver.last_name as receiver_last_name', 'sender.first_name as sender_first_name', 'sender.last_name as sender_last_name')
        //     ->where('sender_id', $request->user()->id)
        //     ->orWhere('receiver_id', $request->user()->id)
        //     // ->orderBy('chatscreated_at', 'desc')
        //     ->get();

        $user_id = $request->user()->id;

        $chats = DB::table('chats')
            ->select(DB::raw('chats.chat_id as chat_id, IF(sender_id != '. $user_id .', sender_id, receiver_id) as user_id, CONCAT(users.first_name, " ", users.last_name) as user_name'))
            ->join('users', function($join) use ($user_id) {
                $join->on('users.id', '=', 'chats.sender_id')
                     ->orWhere('users.id', '=', 'chats.receiver_id')
                     ->where('users.id', '!=', $user_id);
            })
            ->where('sender_id', $user_id)
            ->orWhere('receiver_id', $user_id)
            ->orderBy('chats.created_at', 'desc')
            ->groupBy('user_id')
            ->get();

        return response()->json([
            'message' => 'success',
            'chats' => $chats,
        ]);
    }

    public function saveMessages(Request $request)
    {
        $message = Message::create([
            'message_id' => Str::uuid(),
            'chat_id' => $request->chat_id,
            'message' => $request->message,
        ]);

        $message = DB::table('messages')
            ->join('chats', 'chats.chat_id', '=', 'messages.chat_id')
            ->join('users', 'users.id', '=', 'chats.sender_id')
            ->select('messages.*', 'users.first_name', 'users.last_name')
            ->where('messages.message_id', $message->message_id)
            ->first();

        return response()->json([
            'message' => 'success',
            'chat' => $message,
        ]);
    }
}