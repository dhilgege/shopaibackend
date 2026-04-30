<?php

namespace App\Ai\Agents;

use App\Ai\Middleware\LogPrompts;
use App\Ai\Tools\ProductLookup;
use App\Ai\Tools\ProductSearch;
use App\Ai\Tools\InventoryCheck;
use App\Ai\Tools\RecommendProducts;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasMiddleware;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Concerns\Promptable;

#[Temperature(0.7)]
#[Provider('openai')]
#[Model('gpt-4o-mini')]
class ChatAgent implements Agent, Conversational, HasMiddleware, HasTools
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

    public function tools(): array
    {
        return [
            new ProductSearch(),
            new ProductLookup(),
            new InventoryCheck(),
            new RecommendProducts(),
        ];
    }
}
