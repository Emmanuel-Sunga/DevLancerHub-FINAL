<?php

namespace App\Models;

class Friend
{
    private ?int $id;
    private int $userId;
    private int $friendId;
    private string $status; // 'pending', 'accepted', 'blocked'
    private string $createdAt;
    private ?string $acceptedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->userId = $data['user_id'] ?? 0;
        $this->friendId = $data['friend_id'] ?? 0;
        $this->status = $data['status'] ?? 'pending';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->acceptedAt = $data['accepted_at'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFriendId(): int
    {
        return $this->friendId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getAcceptedAt(): ?string
    {
        return $this->acceptedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'friend_id' => $this->friendId,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'accepted_at' => $this->acceptedAt
        ];
    }
}

