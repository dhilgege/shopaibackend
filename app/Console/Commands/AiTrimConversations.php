<?php

namespace App\Console\Commands;

use App\Models\AgentConversation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AiTrimConversations extends Command
{
    protected $signature = 'ai:trim-conversations {conversation_id : The conversation ID to trim} {keep=20 : Number of recent messages to keep}';
    protected $description = 'Trim old messages from a conversation to reduce token usage';

    public function handle(): int
    {
        $conversationId = $this->argument('conversation_id');
        $keep = (int) $this->argument('keep');

        $conversation = AgentConversation::find($conversationId);

        if (!$conversation) {
            $this->error("Conversation {$conversationId} not found.");
            return self::FAILURE;
        }

        $totalMessages = $conversation->messages()->count();

        if ($totalMessages <= $keep) {
            $this->info("Conversation already has only {$totalMessages} messages (≤ {$keep}). No trimming needed.");
            return self::SUCCESS;
        }

        $messagesToDelete = $conversation->messages()
            ->orderBy('created_at', 'desc')
            ->offset($keep)
            ->limit($totalMessages - $keep)
            ->get();

        $deletedCount = $messagesToDelete->count();
        $ids = $messagesToDelete->pluck('id')->toArray();

        DB::transaction(function () use ($ids) {
            \App\Models\AgentConversationMessage::whereIn('id', $ids)->delete();
        });

        $this->info("Trimmed {$deletedCount} old messages from conversation {$conversationId}. Kept {$keep} most recent messages.");

        return self::SUCCESS;
    }
}
