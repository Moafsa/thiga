<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppConversationContext extends Model
{
    protected $table = 'whatsapp_conversation_contexts';

    protected $fillable = [
        'tenant_id',
        'phone',
        'messages',
        'last_activity_at',
    ];

    protected $casts = [
        'messages' => 'array',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Find context for a given phone/tenant, or return new instance
     */
    public static function getOrNew(int $tenantId, string $phone): self
    {
        return self::firstOrNew(
            ['tenant_id' => $tenantId, 'phone' => $phone],
            ['messages' => [], 'last_activity_at' => now()]
        );
    }

    /**
     * Add a message to the context history
     */
    public function addMessage(array $message): void
    {
        $messages = $this->messages ?? [];
        $messages[] = $message;

        // Keep only the last 20 turns (40 messages: 20 user + 20 assistant)
        if (count($messages) > 40) {
            // Always keep system prompt at index 0, trim oldest messages
            $messages = array_slice($messages, -40);
        }

        $this->messages = $messages;
        $this->last_activity_at = now();
    }

    /**
     * Clear context (e.g., after human transfer or session end)
     */
    public function clearContext(): void
    {
        $this->messages = [];
        $this->last_activity_at = now();
        $this->save();
    }
}
