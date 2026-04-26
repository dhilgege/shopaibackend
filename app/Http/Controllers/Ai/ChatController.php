<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Ai\Agents\ChatAgent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $userId = $validated['user_id'] ?? null;
        $user = $userId ? User::find($userId) : null;

        // Build agent with user context for conversation memory
        $agent = new ChatAgent();
        if ($user) {
            $agent = $agent->forUser($user);
        }

        // Continue existing conversation if ID provided
        if ($conversationId = $validated['conversation_id']) {
            $agent = $agent->continue($conversationId, as: $user);
        }

        try {
            $ai = app(Ai::class);
            $response = $ai->chat($agent, $validated['message']);

            return response()->json([
                'data' => [
                    'message' => (string) $response,
                    'conversation_id' => $response->conversationId,
                ],
            ]);
        } catch (Throwable $e) {
            \Log::error('AI chat error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'data' => [
                    'error' => 'AI service unavailable. Please try again later.',
                    'message' => 'Sorry, I am having trouble responding right now. Please try again in a moment.',
                ],
            ], 503);
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

        if ($conversationId = $validated['conversation_id']) {
            $agent = $agent->continue($conversationId, as: $user);
        }

        try {
            $ai = app(Ai::class);
            $stream = $ai->chatStream($agent, $validated['message']);

            // Return streaming response (SSE)
            return $stream;
        } catch (Throwable $e) {
            \Log::error('AI chat stream error', ['error' => $e->getMessage()]);

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

        $query = \App\Models\AgentConversation::query()->with(['messages' => function ($q) {
            $q->latest()->limit(10);
        }]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $conversations = $query->orderBy('updated_at', 'desc')->paginate(20);

        return response()->json(['data' => $conversations]);
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
