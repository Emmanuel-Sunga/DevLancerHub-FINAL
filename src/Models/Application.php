<?php

namespace App\Models;

class Application
{
    private ?int $id;
    private int $jobId;
    private int $employeeId;
    private string $coverLetter;
    private string $status;
    private string $createdAt;
    private ?string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->jobId = $data['job_id'] ?? 0;
        $this->employeeId = $data['employee_id'] ?? 0;
        $this->coverLetter = $data['cover_letter'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }

    public function getEmployeeId(): int
    {
        return $this->employeeId;
    }

    public function getCoverLetter(): string
    {
        return $this->coverLetter;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->jobId,
            'employee_id' => $this->employeeId,
            'cover_letter' => $this->coverLetter,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}

