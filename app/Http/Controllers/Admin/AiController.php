<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function generateContent(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string|max:5000',
            'isStructured' => 'sometimes|boolean',
            'schema' => 'sometimes|nullable|array', 
        ]);

        $apiKey = config('services.google.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'Google API Key not configured on the server.'], 500);
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
        
        $payload = [
            'contents' => [[
                'role' => 'user',
                'parts' => [['text' => $validated['prompt']]]
            ]]
        ];

        // Dùng isset() để kiểm tra an toàn hơn
        if (isset($validated['isStructured']) && $validated['isStructured'] === true && isset($validated['schema'])) {
             $payload['generationConfig'] = [
                'responseMimeType' => 'application/json',
                'responseSchema' => $validated['schema']
             ];
        }

        try {
            $response = Http::timeout(60)->post($apiUrl, $payload);

            if ($response->successful()) {
                return response()->json($response->json());
            } else {
                Log::error('Gemini API Error:', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['error' => 'Failed to get a response from AI service.', 'details' => $response->json()], $response->status());
            }
        } catch (\Throwable $th) {
            Log::error('Exception calling Gemini API:', ['message' => $th->getMessage()]);
            return response()->json(['error' => 'An internal server error occurred.'], 500);
        }
    }
}