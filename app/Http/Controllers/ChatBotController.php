<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\DialogflowService;
// Conversation persistence removed (FAQ only)

class ChatBotController extends Controller
{
    public function chat(Request $request, DialogflowService $dialogflow)
    {
        $text      = $request->input('message');
        $sessionId = session()->getId();

        try {
            $reply  = $dialogflow->detectIntent($text, $sessionId);
            return response()->json(['reply' => $reply]);
        } catch (\Throwable $e) {
            Log::error('Dialogflow Error: '.$e->getMessage());
            return response()->json([
                'reply'   => 'Pasensya, may problema sa chatbot.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
