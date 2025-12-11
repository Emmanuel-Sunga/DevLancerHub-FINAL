<?php

namespace App\Services;

use App\Models\Friend;

class FriendService
{
    private JsonDatabase $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['friends_file']);
    }

    public function sendFriendRequest(int $userId, int $friendId): Friend
    {
        // Check if request already exists
        $existing = $this->db->findWhere(fn($f) => 
            ($f['user_id'] === $userId && $f['friend_id'] === $friendId) ||
            ($f['user_id'] === $friendId && $f['friend_id'] === $userId)
        );

        if (!empty($existing)) {
            throw new \Exception('Friend request already exists');
        }

        $data = [
            'user_id' => $userId,
            'friend_id' => $friendId,
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->create($data);
        $data['id'] = $id;

        return new Friend($data);
    }

    public function acceptFriendRequest(int $requestId): bool
    {
        $friend = $this->db->findById($requestId);
        if (!$friend || $friend['status'] !== 'pending') {
            return false;
        }

        return $this->db->update($requestId, [
            'status' => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejectFriendRequest(int $requestId): bool
    {
        return $this->db->delete($requestId);
    }

    public function getFriends(int $userId): array
    {
        return array_map(fn($data) => new Friend($data), 
            $this->db->findWhere(fn($f) => ($f['user_id'] === $userId || $f['friend_id'] === $userId) && $f['status'] === 'accepted')
        );
    }

    public function getPendingRequests(int $userId): array
    {
        return array_map(fn($data) => new Friend($data), 
            $this->db->findWhere(fn($f) => $f['friend_id'] === $userId && $f['status'] === 'pending')
        );
    }

    public function getSentRequests(int $userId): array
    {
        return array_map(fn($data) => new Friend($data), 
            $this->db->findWhere(fn($f) => $f['user_id'] === $userId && $f['status'] === 'pending')
        );
    }

    public function isFriend(int $userId, int $friendId): bool
    {
        return !empty($this->db->findWhere(fn($f) => 
            (($f['user_id'] === $userId && $f['friend_id'] === $friendId) ||
             ($f['user_id'] === $friendId && $f['friend_id'] === $userId)) &&
            $f['status'] === 'accepted'
        ));
    }

    public function removeFriend(int $requestId): bool
    {
        return $this->db->delete($requestId);
    }
}

