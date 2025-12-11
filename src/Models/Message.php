<?php

namespace App\Models;

class Message
{
    private ?int $id;
    private int $senderId;
    private int $receiverId;
    private string $message;
    private bool $isRead;
    private string $createdAt;
    private ?string $readAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->senderId = $data['sender_id'] ?? 0;
        $this->receiverId = $data['receiver_id'] ?? 0;
        $this->message = $data['message'] ?? '';
        $this->isRead = $data['is_read'] ?? false;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->readAt = $data['read_at'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getReceiverId(): int
    {
        return $this->receiverId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getReadAt(): ?string
    {
        return $this->readAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'sender_id' => $this->senderId,
            'receiver_id' => $this->receiverId,
            'message' => $this->message,
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt,
            'read_at' => $this->readAt
        ];
    }
}

