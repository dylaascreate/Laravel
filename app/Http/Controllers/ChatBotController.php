<?php

namespace App\Http\Controllers;

use App\Services\FlaskService;
use Exception; // New Import
use Illuminate\Http\Request;

class ChatBotController extends Controller
{
    protected $flaskService;

    public function __construct(FlaskService $flaskService)
    {
        $this->flaskService = $flaskService;
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        try {
            // Simplified call
            $reply = $this->flaskService->askAssistant($validated['message']);

            return response()->json(['success' => true, 'reply' => $reply]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 503);
        }
    }
}
