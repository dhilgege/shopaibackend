<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
Schema::create('agent_conversation_messages', function (Blueprint $table) {
    $table->id();

    $table->foreignId('conversation_id')
          ->constrained('agent_conversations')
          ->onDelete('cascade');

    $table->string('role');
    $table->text('content');
    $table->json('metadata')->nullable();
    $table->timestamp('created_at')->useCurrent();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_conversation_messages');
    }
};
