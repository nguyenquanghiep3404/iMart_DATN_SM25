<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    /**
     * Xử lý yêu cầu tạo nội dung từ Gemini AI.
     * Method này có thể xử lý hai loại yêu cầu:
     * 1. Yêu cầu trò chuyện (từ chatbot) dựa trên 'chatHistory'.
     * 2. Yêu cầu tạo nội dung một lần (từ admin) dựa trên 'prompt', có thể kèm theo 'schema' để trả về JSON.
     */
    public function generateContent(Request $request)
    {
        // 1. VALIDATION ĐA NĂNG
        // Yêu cầu phải có 'prompt' HOẶC 'chatHistory', nhưng không phải cả hai.
        $validated = $request->validate([
            // Dành cho admin/tạo dữ liệu một lần
            'prompt' => 'sometimes|required_without:chatHistory|string|max:5000',
            'isStructured' => 'sometimes|boolean',
            'schema' => 'sometimes|nullable|array',
            
            // Dành cho chatbot
            'chatHistory' => 'sometimes|required_without:prompt|array',
        ]);

        $apiKey = config('services.google.api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'Google API Key not configured on the server.'], 500);
        }

        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
        
        $payload = [];

        // 2. PHÂN LUỒNG LOGIC
        // Kiểm tra xem yêu cầu này là từ chatbot hay từ admin
        if ($request->has('chatHistory')) {
            // ----- LOGIC DÀNH CHO CHATBOT -----
            $payload = [
                'contents' => $validated['chatHistory'], // Sử dụng toàn bộ lịch sử trò chuyện
                'generationConfig' => [ // Cấu hình cho việc trò chuyện
                    'temperature' => 0.7,
                    'topK' => 1,
                    'topP' => 1,
                    'maxOutputTokens' => 2048,
                ],
            ];
        } 
        elseif ($request->has('prompt')) {
            // ----- LOGIC CŨ DÀNH CHO ADMIN/TẠO DỮ LIỆU CÓ CẤU TRÚC -----
            $payload = [
                'contents' => [[
                    'role' => 'user',
                    'parts' => [['text' => $validated['prompt']]]
                ]]
            ];

            // Nếu là yêu cầu tạo JSON có cấu trúc, thêm cấu hình responseSchema
            if (isset($validated['isStructured']) && $validated['isStructured'] === true && isset($validated['schema'])) {
                $payload['generationConfig'] = [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $validated['schema']
                ];
            }
        } else {
            // Trường hợp không có cả 'prompt' và 'chatHistory', trả về lỗi.
            // Validation của Laravel thường sẽ bắt lỗi này trước.
            return response()->json(['error' => 'Request must contain either a "prompt" or "chatHistory".'], 422);
        }

        // 3. GỌI API (DÙNG CHUNG CHO CẢ HAI LUỒNG)
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