<?php

namespace App\Services;

use App\Models\Message;

class MessageService
{
    private JsonDatabase $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['messages_file']);
    }

    public function sendMessage(int $senderId, int $receiverId, string $message): Message
    {
        $data = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => trim($message),
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->create($data);
        $data['id'] = $id;

        return new Message($data);
    }

    public function getConversation(int $userId1, int $userId2): array
    {
        $messages = $this->db->findWhere(function($message) use ($userId1, $userId2) {
            return ($message['sender_id'] === $userId1 && $message['receiver_id'] === $userId2) ||
                   ($message['sender_id'] === $userId2 && $message['receiver_id'] === $userId1);
        });

        // Sort by created_at
        usort($messages, fn($a, $b) => strtotime($a['created_at']) - strtotime($b['created_at']));

        return array_map(fn($data) => new Message($data), $messages);
    }

    public function getConversations(int $userId): array
    {
        $allMessages = $this->db->readAll();
        $conversations = [];
        $userIds = [];
        $lastMessages = [];

        foreach ($allMessages as $message) {
            $otherUserId = $message['sender_id'] === $userId ? $message['receiver_id'] : 
                          ($message['receiver_id'] === $userId ? $message['sender_id'] : null);

            if ($otherUserId) {
                // Track last message per user
                if (!isset($lastMessages[$otherUserId]) || 
                    strtotime($message['created_at']) > strtotime($lastMessages[$otherUserId]['created_at'])) {
                    $lastMessages[$otherUserId] = $message;
                }
                
                if (!in_array($otherUserId, $userIds)) {
                    $userIds[] = $otherUserId;
                }
            }
        }

        // Build conversations array
        foreach ($userIds as $otherUserId) {
            $conversations[] = [
                'user_id' => $otherUserId,
                'last_message' => new Message($lastMessages[$otherUserId]),
                'unread_count' => $this->getUnreadCount($userId, $otherUserId)
            ];
        }

        // Sort by last message time
        usort($conversations, fn($a, $b) => 
            strtotime($b['last_message']->getCreatedAt()) - strtotime($a['last_message']->getCreatedAt())
        );

        return $conversations;
    }

    public function markAsRead(int $messageId): bool
    {
        $message = $this->db->findById($messageId);
        if (!$message) {
            return false;
        }

        return $this->db->update($messageId, [
            'is_read' => true,
            'read_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function markConversationAsRead(int $userId, int $otherUserId): bool
    {
        $messages = $this->db->findWhere(fn($m) => 
            $m['receiver_id'] === $userId && 
            $m['sender_id'] === $otherUserId &&
            $m['is_read'] === false
        );

        foreach ($messages as $message) {
            $this->db->update($message['id'], [
                'is_read' => true,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }

        return true;
    }

    public function getUnreadCount(int $userId, ?int $senderId = null): int
    {
        $messages = $this->db->findWhere(fn($m) => 
            $m['receiver_id'] === $userId && 
            $m['is_read'] === false &&
            ($senderId === null || $m['sender_id'] === $senderId)
        );

        return count($messages);
    }

    public function getTotalUnreadCount(int $userId): int
    {
        return $this->getUnreadCount($userId);
    }
}

