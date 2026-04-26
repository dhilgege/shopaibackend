<?php

namespace App\Console\Commands;

use App\Models\AgentConversation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AiCleanConversations extends Command
{
    protected $signature = 'ai:clean-conversations {days=30 : Delete conversations older than this many days}';
    protected $description = 'Delete old AI conversations and their messages';

    public function handle(): int
    {
        $days = $this->argument('days');
        $cutoff = now()->subDays($days);

        $count = DB::transaction(function () use ($cutoff) {
            $oldConversations = AgentConversation::where('updated_at', '<', $cutoff)->get();
            $deletedMessages = 0;
            $deletedConversations = 0;

            foreach ($oldConversations as $conversation) {
                $deletedMessages += $conversation->messages()->count();
                $conversation->messages()->delete();
                $conversation->delete();
                $deletedConversations++;
            }

            return ['conversations' => $deletedConversations, 'messages' => $deletedMessages];
        });

        $this->info("Deleted {$count['conversations']} conversations and {$count['messages']} messages older than {$days} days.");

        return self::SUCCESS;
    }
}
