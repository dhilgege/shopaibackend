/# ShopAI E-commerce System - Overview

## System Architecture

```
Frontend (Flutter)           Backend (Laravel)           AI Service (Ollama)
    │                              │                              │
    │ 1. HTTP Request              │                              │
    │─────────────────────────────>│                              │
    │                              │                              │
    │                              │ 2. Process Request          │
    │                              │    - Validation             │
    │                              │    - Database Operations    │
    │                              │                              │
    │                              │ 3. Return Response          │
    │<─────────────────────────────│                              │
    │                              │                              │
    │                              │                              │
    │ 4. Display Data              │                              │
    │                              │                              │
    │                              │                              │
    │ 5. AI Chat Request           │                              │
    │─────────────────────────────>│                              │
    │                              │                              │
    │                              │ 6. Forward to Ollama        │
    │                              │─────────────────────────────>│
    │                              │                              │
    │                              │ 7. Get AI Response          │
    │                              │<─────────────────────────────│
    │                              │                              │
    │ 8. Display AI Response       │                              │
    │<─────────────────────────────│                              │
```

## Technology Stack

### Backend
- **Framework:** Laravel 13
- **Language:** PHP 8.3
- **Database:** MySQL
- **AI Provider:** Ollama (Qwen2.5:0.5b)

### Frontend
- **Framework:** Flutter 3.19+
- **Language:** Dart
- **State Management:** BLoC Pattern
- **HTTP Client:** Dio
- **Dependency Injection:** GetIt

## Key Design Decisions

### 1. Simplified Product Model
- Removed complex relationships (categories table, foreign keys)
- Category as string field for flexibility
- Core e-commerce fields only

### 2. No Pagination
- Simple list-based approach
- Better for small to medium catalogs
- Reduces frontend complexity

### 3. Clean Architecture
- Domain-driven design
- Testable components
- Scalable structure

### 4. BLoC Pattern
- Predictable state management
- Separation of business logic from UI
- Easy testing

### 5. AI Integration
- Basic chat interface
- Ollama for local AI
- Extensible for future tools

## File Structure

### Backend
```
shopai/
├── app/
│   ├── Models/          # Eloquent models
│   ├── Http/Controllers/Api/  # API controllers
│   ├── Ai/
│   │   ├── Agents/      # AI agents
│   │   ├── Middleware/  # AI middleware
│   │   └── Tools/       # AI tools (removed in refactor)
│   └── Models/          # Domain models
├── database/
│   ├── migrations/      # Database migrations
│   └── factories/       # Model factories
├── routes/
│   └── api.php          # API routes
└── config/
    └── ai.php           # AI configuration
```

### Frontend
```
shopai_fe/
├── lib/
│   ├── core/
│   │   ├── error/       # Failure & Exception classes
│   │   ├── network/     # Dio client
│   │   └── theme/       # App theme
│   ├── features/
│   │   └── product/
│   │       ├── domain/
│   │       │   ├── entities/      # Domain entities
│   │       │   ├── repositories/  # Repository interfaces
│   │       │   └── usecases/      # Use cases
│   │       ├── data/
│   │       │   ├── models/        # DTOs
│   │       │   ├── datasources/   # Remote data sources
│   │       │   └── repositories/  # Repository implementations
│   │       └── presentation/
│   │           ├── bloc/          # BLoC & States
│   │           ├── pages/         # Screens
│   │           └── widgets/       # Reusable widgets
│   └── main.dart      # App entry point
└── pubspec.yaml        # Dependencies
```

## API Documentation

### Products

#### List All Products
```
GET /api/products

Response:
{
  "data": [
    {
      "id": 1,
      "name": "Smartphone X",
      "description": "Latest smartphone",
      "price": 699.99,
      "stock": 50,
      "image_url": "https://...",
      "category": "Electronics",
      "created_at": "2024-01-15T10:30:00.000000Z",
      "updated_at": "2024-01-15T10:30:00.000000Z"
    }
  ]
}
```

#### Create Product
```
POST /api/products

Body:
{
  "name": "Smartphone X",
  "description": "Latest smartphone",
  "price": 699.99,
  "stock": 50,
  "image_url": "https://...",
  "category": "Electronics"
}

Response: 201 Created
```

### AI Chat

#### Send Message
```
POST /ai/chat

Body:
{
  "message": "Show me available products",
  "conversation_id": "optional",
  "user_id": "optional"
}

Response:
{
  "data": {
    "message": "Here are the available products...",
    "conversation_id": "123"
  }
}
```

## Setup Instructions

### Prerequisites
- PHP 8.3+
- Composer
- Flutter 3.19+
- Ollama (optional)

### Backend Setup

1. Install dependencies:
```bash
cd /home/dhilgege/projectt/shopai
composer install
```

2. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Update `.env`:
```
OLLAMA_URL=http://127.0.0.1:11434
```

4. Run migrations:
```bash
php artisan migrate
```

5. Seed database (optional):
```bash
php seed.php
```

6. Start server:
```bash
php artisan serve
```

### Frontend Setup

1. Install dependencies:
```bash
cd /home/dhilgege/projectt/shopai_fe
flutter pub get
```

2. Run application:
```bash
flutter run
```

### Ollama Setup (Optional)

1. Install Ollama:
```bash
curl -fsSL https://ollama.com/install.sh | sh
```

2. Start Ollama:
```bash
ollama serve
```

3. Pull model:
```bash
ollama run qwen2.5:0.5b
```

## Testing

### Backend Tests
```bash
cd /home/dhilgege/projectt/shopai
php artisan test
```

### Frontend Tests
```bash
cd /home/dhilgege/projectt/shopai_fe
flutter test
```

## Future Enhancements

1. **User Authentication**
   - JWT or Sanctum
   - Protected routes

2. **Shopping Cart**
   - Add to cart functionality
   - Checkout process

3. **Advanced AI**
   - Product search tool
   - Inventory check tool
   - Order creation tool

4. **Product Categories**
   - Dedicated categories table
   - Filter by category

5. **Search & Filter**
   - Full-text search
   - Price range filter
   - Category filter

6. **Pagination**
   - Infinite scroll
   - Load more

7. **Image Upload**
   - Product images
   - Cloud storage

## Troubleshooting

### Backend Won't Start
- Check PHP version: `php -v`
- Check dependencies: `composer install`
- Check .env configuration

### Frontend Won't Start
- Run: `flutter pub get`
- Check Flutter version: `flutter doctor`
- Clean build: `flutter clean`

### AI Not Responding
- Check Ollama is running: `ollama serve`
- Check model is pulled: `ollama list`
- Check .env URL is correct

### API Connection Failed
- Check backend is running: `php artisan serve`
- Check CORS settings
- Check network configuration

## Contributing

1. Follow existing code style
2. Write tests for new features
3. Update documentation
4. Submit pull request

## License

MIT License
