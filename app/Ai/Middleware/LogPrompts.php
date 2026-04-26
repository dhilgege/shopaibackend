<?php

namespace App\Ai\Middleware;

use Laravel\Ai\Contracts\AiRequest;
use Laravel\Ai\Contracts\Middleware;
use Laravel\Ai\Contracts\Response as AiResponse;
use Illuminate\Support\Facades\Log;

class LogPrompts implements Middleware
{
    public function handle(AiRequest $request, callable $next): AiResponse
    {
        Log::info('AI Prompt', [
            'prompt' => $request->prompt(),
            'context' => $request->context(),
        ]);

        $response = $next($request);

        Log::info('AI Response', [
            'response' => $response->content(),
        ]);

        return $response;
    }
}
