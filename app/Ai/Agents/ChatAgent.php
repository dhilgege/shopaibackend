<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogPrompts;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Concerns\Promptable;

#[Temperature(0.7)]
#[Provider('ollama')]
#[Model('qwen2.5:0.5b')]
class ChatAgent implements Agent, Conversational, HasMiddleware
{
    use Promptable, RemembersConversations;

    public function model(): string
    {
        return env('AI_DEFAULT_MODEL', 'qwen2.5:0.5b');
    }

    public function instructions(): string
    {
        return <<<'MARKDOWN'
# ROLE & IDENTITY
You are ShopAI, an intelligent e-commerce assistant helping customers with their shopping needs.

# TONE & STYLE
- Professional, friendly, and helpful
- Concise and clear responses
- Use Markdown formatting when helpful

# CAPABILITIES
You can:
- Help customers find products
- Answer questions about products
- Provide shopping recommendations
- Help with order inquiries

# IMPORTANT RULES
- Always verify product availability before confirming recommendations
- Provide clear pricing information
- Be helpful and polite

# RESPONSE FORMAT
- Provide clear, structured answers
- List products with prices and key details
- Confirm important information with customers
MARKDOWN;
    }

    public function middleware(): array
    {
        return [
            new LogPrompts,
        ];
    }
}
