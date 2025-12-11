<?php

namespace App\Controllers;

use App\Services\MessageService;
use App\Services\AuthService;
use App\Middleware\SessionMiddleware;

class MessageController
{
    private MessageService $messageService;
    private AuthService $authService;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->authService = new AuthService();
    }

    public function send(): array
    {
        try {
            $senderId = SessionMiddleware::getUserId();
            $receiverId = (int)$_POST['receiver_id'];
            $message = trim($_POST['message'] ?? '');

            if (empty($message)) {
                return [
                    'success' => false,
                    'errors' => ['message' => 'Message cannot be empty']
                ];
            }

            $this->messageService->sendMessage($senderId, $receiverId, $message);

            return [
                'success' => true,
                'redirect' => 'messages.php?user_id=' . $receiverId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function conversations(): array
    {
        $userId = SessionMiddleware::getUserId();
        $conversations = $this->messageService->getConversations($userId);

        // Batch load user details to reduce queries
        $userIds = array_column($conversations, 'user_id');
        $users = [];
        foreach ($userIds as $id) {
            $users[$id] = $this->authService->getUserById($id);
        }
        
        $conversationsWithDetails = array_filter(array_map(function($conv) use ($users) {
            return ($user = $users[$conv['user_id']] ?? null) ? [
                'user' => $user,
                'last_message' => $conv['last_message'],
                'unread_count' => $conv['unread_count']
            ] : null;
        }, $conversations));

        return [
            'conversations' => $conversationsWithDetails,
            'total_unread' => $this->messageService->getTotalUnreadCount($userId)
        ];
    }

    public function conversation(int $otherUserId): array
    {
        $userId = SessionMiddleware::getUserId();
        $otherUser = $this->authService->getUserById($otherUserId);

        if (!$otherUser) {
            return [
                'error' => 'User not found'
            ];
        }

        $messages = $this->messageService->getConversation($userId, $otherUserId);
        
        // Mark messages as read
        $this->messageService->markConversationAsRead($userId, $otherUserId);

        return [
            'other_user' => $otherUser,
            'messages' => $messages
        ];
    }
}

