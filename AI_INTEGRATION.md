# ShopAI - Laravel AI SDK Integration Guide

## Overview
This document describes the integration of the Laravel AI SDK into ShopAI, enabling real AI-powered chat using Qwen2.5:0.5b model via Ollama at `100.82.100.80:11434`.

---

## Architecture

### Backend (Laravel)

**AI Components:**
- `app/Ai/Agents/ChatAgent.php` - Main conversational agent with spare parts expertise
- `app/Ai/Tools/` - Custom tools for the agent:
  - `ProductSearch` - Search products by name, brand, part number, vehicle model
  - `ProductLookup` - Get detailed product info by ID/SKU
  - `InventoryCheck` - Check stock levels
  - `RecommendProducts` - AI-powered recommendations
  - `OrderCreate` - Create orders (with stock validation)
  - `OrderLookup` - Retrieve order history/status
- `app/Http/Controllers/Ai/ChatController.php` - REST endpoints for chat
- `config/ai.php` - AI provider configuration (Ollama at 100.82.100.80:11434)
- Database tables: `agent_conversations`, `agent_conversation_messages`

**Routes (routes/api.php):**
```
POST   /api/ai/chat                - Send message (non-streaming)
POST   /api/ai/chat/stream         - Stream response (SSE)
GET    /api/ai/conversations       - List user's conversations
GET    /api/ai/conversations/{id}  - Get conversation with messages
DELETE /api/ai/conversations/{id}  - Delete conversation
```

### Frontend (Flutter)

**AI Chat Components:**
- `lib/presentation/pages/chat_ai_screen.dart` - Chat UI with message bubbles
- `lib/presentation/bloc/chat/chat_bloc.dart` - State management for chat
- `lib/data/datasources/remote/chat_remote_datasource.dart` - HTTP client calling `/api/ai/chat`
- `lib/data/repositories/chat_repository_impl.dart` - Repository pattern
- `lib/domain/usecases/send_chat_message.dart` - Business logic use case

**Dependencies:**
- `flutter_markdown` - Renders AI responses with markdown formatting
- `dio` - HTTP client
- `bloc` - State management

---

## Installation & Setup

### 1. Backend: Ollama Server

The AI provider uses **Ollama** running at `100.82.100.80:11434`. Ensure Ollama is installed and the Qwen2.5:0.5b model is available:

```bash
# On the Ollama server (100.82.100.80)
ollama pull qwen2.5:0.5b
ollama serve  # Should be running on port 11434
```

Test access from your backend machine:
```bash
curl http://100.82.100.80:11434/api/tags
# Should list available models including qwen2.5:0.5b
```

### 2. Backend: Laravel Configuration

The configuration is already in place:
- `config/ai.php` - Sets Ollama driver and URL `http://100.82.100.80:11434`
- `.env` - `AI_PROVIDER=ollama`, `AI_DEFAULT_MODEL=qwen2.5:0.5b`

If you need to adjust:
```env
AI_PROVIDER=ollama
AI_DEFAULT_MODEL=qwen2.5:0.5b
OLLAMA_URL=http://100.82.100.80:11434
AI_TIMEOUT=120
```

### 3. Backend: Start Server

```bash
cd /home/dhilgege/projectt/shopai
php artisan serve --host=127.0.0.1 --port=8000
```

Verify AI endpoint:
```bash
curl -X POST http://127.0.0.1:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message":"Hello","user_id":1}'
```
Expected response:
```json
{
  "data": {
    "message": "Hello! How can I help you with your shopping today?",
    "conversation_id": "uuid-string"
  }
}
```

### 4. Frontend: Get Dependencies

```bash
cd /home/dhilgege/projectt/shopai_fe
flutter pub get
```

### 5. Frontend: Run App

```bash
flutter run
```

Navigate to **AI Assistant** from the home screen quick actions or profile menu.

---

## How It Works

### Conversation Flow

1. **User types message** → `SendMessage` event dispatched
2. **ChatBloc** adds user message to state, emits `ChatLoading`
3. **SendChatMessageUseCase** calls `ChatRepository`
4. **ChatRepository** calls `ChatRemoteDatasource`
5. **Dio** POSTs to `/api/ai/chat` with `{message, user_id, conversation_id?}`
6. **Laravel ChatAgent** processes via Laravel AI SDK:
   - Loads conversation history (if `conversation_id` provided)
   - Determines which tools to use
   - Calls Ollama API at 100.82.100.80:11434
   - Executes tools (product search, inventory check, etc.)
   - Returns final response
7. **Response** bubbles back to Flutter
8. **ChatBloc** emits `ChatMessageSent` with AI response
9. **UI** renders markdown-formatted AI message

### Tools Available to AI

| Tool | Purpose | Parameters |
|------|---------|------------|
| ProductSearch | Find products by name/brand/part number/vehicle | `query`, `filters` (category_id, brand, vehicle_model, in_stock), `limit` |
| ProductLookup | Get detailed product info | `product_id` or `sku` |
| InventoryCheck | Check stock levels | None (filters by low stock) |
| RecommendProducts | Get AI recommendations | `strategy`, `limit` |
| OrderCreate | Create a new order | `items` (array of product_id + quantity), `user_id`, `order_date` |
| OrderLookup | Retrieve orders | `order_id`, `user_id`, `date_range` |

The AI agent automatically chooses which tool to use based on user query.

### Example Interactions

**Product Search:**
```
User: "I need a brake pad for Toyota Camry 2020"
AI: Searches products → Finds brake pads matching Toyota → Shows list with prices/stock
```

**Inventory Check:**
```
User: "What's in stock?"
AI: Calls InventoryCheck → Lists low stock items & available inventory
```

**Order Creation:**
```
User: "Order 2 units of product #5"
AI: Checks stock → Confirms with user → Creates order → Updates inventory
```

**Recommendations:**
```
User: "Recommend something for Ford F-150"
AI: Searches for compatible parts → Suggests bestsellers or complementary items
```

---

## API Reference

### POST /api/ai/chat

**Request:**
```json
{
  "message": "string",
  "user_id": 1,
  "conversation_id": "uuid (optional)"
}
```

**Response (200 OK):**
```json
{
  "data": {
    "message": "AI response text...",
    "conversation_id": "uuid-string"
  }
}
```

**Error Response (503):**
```json
{
  "data": {
    "error": "AI service unavailable.",
    "message": "Sorry, I am having trouble responding right now..."
  }
}
```

### Streaming: POST /api/ai/chat/stream

Returns Server-Sent Events (SSE) stream. Not implemented in Flutter yet.

---

## Data Models

### Flutter Message Format (in-memory)
```dart
{
  'text': 'Message content',
  'isUser': bool,  // true = user, false = AI
  'timestamp': 'ISO8601 string',
}
```

### Conversation Persistence

Conversations are stored in Laravel database:
- `agent_conversations` - one per chat session
- `agent_conversation_messages` - each message exchanged

When user sends a message with `conversation_id`, previous context is loaded automatically. When conversation_id is null, a new conversation is created.

---

## Error Handling

**Backend errors:**
- Returns 503 with error message
- Errors logged to Laravel logs (`storage/logs/laravel.log`)

**Flutter errors:**
- `ChatError` state shows red error banner with retry
- Tapping error dismisses it, user can retry

---

## Customization

### Change AI Model

Edit `.env`:
```env
AI_DEFAULT_MODEL=qwen2.5:0.5b  # Change to any Ollama model
```

Or override per-agent in `ChatAgent.php`:
```php
#[Model('qwen2.5:1.5b')]
```

### Change AI Provider (e.g., OpenAI)

1. Update `.env`:
```env
AI_PROVIDER=openai
OPENAI_API_KEY=sk-...
OPENAI_URL=https://api.openai.com/v1
AI_DEFAULT_MODEL=gpt-4o
```

2. Update `config/ai.php` if needed.

### Modify Agent Instructions

Edit `ChatAgent::instructions()` method. The system prompt defines the AI's personality, capabilities, and tool usage guidelines.

### Add New Tools

1. Create tool class: `php artisan make:tool ProductRecommend`
2. Implement `Tool` contract with `description()`, `handle()`, `schema()`
3. Register in `ChatAgent::tools()`

---

## Troubleshooting

### "AI service unavailable" error
- Check Ollama is running: `curl http://100.82.100.80:11434/api/tags`
- Verify model exists: `ollama list`
- Check Laravel logs: `tail -f storage/logs/laravel.log`

### No response / timeout
- Increase `AI_TIMEOUT` in `.env` (default 120s)
- Check network connectivity to 100.82.100.80:11434

### Tool not working
- Ensure tool is registered in `ChatAgent::tools()`
- Check tool's `userId` binding (`->forUser($userId)`)
- Verify database tables exist (products, orders)

### Conversation not persisting
- Ensure `AgentConversation` and `AgentConversationMessage` migrations ran
- Check `user_id` is passed to agent via `forUser($user)`

---

## Files Modified/Created

### Backend
- `config/ai.php` (created)
- `.env` (updated AI_* variables)
- `app/Ai/Agents/ChatAgent.php` (enhanced with proper SDK attributes)
- `app/Http/Controllers/Ai/ChatController.php` (fixed to use SDK properly)

### Frontend
- `lib/data/datasources/remote/chat_remote_datasource.dart` (new)
- `lib/data/repositories/chat_repository_impl.dart` (new)
- `lib/domain/repositories/chat_repository.dart` (new)
- `lib/domain/usecases/send_chat_message.dart` (new)
- `lib/presentation/bloc/chat/chat_bloc.dart` (rewritten for real API)
- `lib/presentation/bloc/chat/chat_state.dart` (added conversationId)
- `lib/presentation/pages/chat_ai_screen.dart` (completely redesigned with markdown)
- `pubspec.yaml` (added flutter_markdown dependency)

---

## Testing

### Unit Test (Backend)
```bash
php artisan test --filter=ChatAgent
```

### Manual Test (API)
```bash
# Start backend
php artisan serve

# Send chat message
curl -X POST http://127.0.0.1:8000/api/ai/chat \
  -H "Content-Type: application/json" \
  -d '{"message":"Find brake pads for Honda", "user_id":1}'
```

### Manual Test (Flutter)
1. Run app: `flutter run`
2. Tap "AI Assistant" from home screen
3. Type: "Can you recommend some products for me?"
4. Expected: AI searches and returns product suggestions with markdown table

---

## Performance Notes

- **First request** may take 5-10 seconds (loading Ollama model into memory)
- **Subsequent requests** are faster (~1-3 seconds)
- Consider enabling **caching** for frequent queries
- For production, use **queue** (`->queue($message)`) for async processing

---

## Security Considerations

- Currently uses **guest user (ID 1)** for all requests
- For multi-user apps, implement authentication and pass actual `user_id`
- Tools scope data by `user_id` to prevent cross-user data leakage
- Consider rate limiting AI endpoints
- Validate and sanitize tool inputs in production

---

## Next Steps

- [ ] Implement streaming responses (SSE) for real-time typing effect
- [ ] Persist conversation history in Flutter (load from `/api/ai/conversations`)
- [ ] Add voice input (speech-to-text)
- [ ] Support image attachments (product images in chat)
- [ ] Add AI-powered inventory insights widget
