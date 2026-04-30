<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Throwable;

class ChatController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $message = strtolower($validated['message']);

            /*
            |--------------------------------------------------------------------------
            | Detect Generic Product Query
            |--------------------------------------------------------------------------
            */
            $genericQueries = [
                'produk',
                'product',
                'tersedia',
                'available',
                'list',
                'apa saja',
                'semua',
                'catalog',
                'katalog',
            ];

            $isGeneric = false;

            foreach ($genericQueries as $keyword) {
                if (str_contains($message, $keyword)) {
                    $isGeneric = true;
                    break;
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Fetch Products
            |--------------------------------------------------------------------------
            */
            if ($isGeneric) {
                $products = Product::latest()
                    ->limit(10)
                    ->get()
                    ->toArray();
            } else {
                $products = Product::where('name', 'like', "%{$message}%")
                    ->orWhere('description', 'like', "%{$message}%")
                    ->orWhere('category', 'like', "%{$message}%")
                    ->limit(10)
                    ->get()
                    ->toArray();

                // fallback kalau tidak ketemu
                if (empty($products)) {
                    $products = Product::latest()
                        ->limit(5)
                        ->get()
                        ->toArray();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Build Prompt
            |--------------------------------------------------------------------------
            */
            $systemPrompt = "
You are ShopAI, an intelligent e-commerce assistant.

RULES:
- Only answer using provided product data
- Never hallucinate products
- Be concise and structured
- Max 6-10 lines
- If products exist, recommend them naturally
- Mention stock if available
- Mention price in Indonesian Rupiah format

PRODUCT DATA:
" . json_encode($products, JSON_PRETTY_PRINT);

            /*
            |--------------------------------------------------------------------------
            | Call Gemini API
            |--------------------------------------------------------------------------
            */
            $response = Http::post(
                "https://generativelanguage.googleapis.com/v1beta/models/" .
                env('AI_DEFAULT_MODEL', 'gemini-1.5-flash') .
                ":generateContent?key=" . env('GEMINI_API_KEY'),
                [
                    "contents" => [
                        [
                            "parts" => [
                                [
                                    "text" => $systemPrompt . "\n\nUSER QUESTION: " . $validated['message']
                                ]
                            ]
                        ]
                    ]
                ]
            );

            $json = $response->json();

            $text =
                $json['candidates'][0]['content']['parts'][0]['text']
                ?? $json['error']['message']
                ?? 'AI tidak merespon';

            return response()->json([
                'reply' => $text,
                'conversation_id' => $validated['conversation_id'] ?? null,
                'quick_replies' => [
                    "🔥 Rekomendasi",
                    "💸 Produk Termurah",
                    "⚡ Produk Terlaris",
                    "📱 Smartphone",
                    "🎮 Gaming",
                    "📊 Bandingkan Produk"
                ]
            ]);

        } catch (Throwable $e) {
            \Log::error($e);

            return response()->json([
                'reply' => 'Server error: ' . $e->getMessage(),
                'quick_replies' => []
            ], 500);
        }
    }
}