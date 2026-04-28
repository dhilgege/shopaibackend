<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Ai\Agents\ChatAgent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Ai;
use Throwable;

class ChatController extends Controller
{
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|exists:agent_conversations,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        try {
            $response = Http::post(
                "https://generativelanguage.googleapis.com/v1beta/models/" .
                env('AI_DEFAULT_MODEL', 'gemini-2.5-flash') .
                ":generateContent?key=" . env('GEMINI_API_KEY'),
                [
                    "contents" => [
                        [
                            "parts" => [
                                ["text" => $validated['message']]
                            ]
                        ]
                    ]
                ]
            );

            $text = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? 'No response';

            return response()->json([
                'data' => [
                    'message' => $text,
                    'conversation_id' => null,
                ],
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'data' => [
                    'message' => 'AI error',
                ],
            ], 500);
        }
    }

    public function chatStream(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'conversation_id' => 'nullable|exists:agent_conversations,id',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $userId = $validated['user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;

        $agent = new ChatAgent();

        if ($user) {
            $agent = $agent->forUser($user);
        }

        if (!empty($validated['conversation_id'])) {
            $agent = $agent->continue($validated['conversation_id'], as: $user);
        }

        try {
            $ai = app(Ai::class);
            return $ai->chatStream($agent, $validated['message']);
        } catch (Throwable $e) {
            return response()->json([
                'data' => [
                    'error' => 'AI service unavailable.',
                ],
            ], 503);
        }
    }

    public function conversations(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');

        $query = \App\Models\AgentConversation::query()
            ->with(['messages' => function ($q) {
                $q->latest()->limit(10);
            }]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return response()->json([
            'data' => $query->orderBy('updated_at', 'desc')->paginate(20)
        ]);
    }

    public function show(string $conversation): JsonResponse
    {
        $conversation = \App\Models\AgentConversation::with('messages')
            ->findOrFail($conversation);

        return response()->json(['data' => $conversation]);
    }

    public function destroy(string $conversation): JsonResponse
    {
        $conversation = \App\Models\AgentConversation::findOrFail($conversation);
        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json(null, 204);
    }
}