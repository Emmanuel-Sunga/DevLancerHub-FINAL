<?php

namespace App\Models;

class Payment
{
    private ?int $id;
    private int $jobId;
    private int $employerId;
    private int $employeeId;
    private float $amount;
    private string $status; // 'pending', 'completed', 'cancelled'
    private string $paymentMethod;
    private string $description;
    private string $createdAt;
    private ?string $completedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->jobId = $data['job_id'] ?? 0;
        $this->employerId = $data['employer_id'] ?? 0;
        $this->employeeId = $data['employee_id'] ?? 0;
        $this->amount = (float)($data['amount'] ?? 0);
        $this->status = $data['status'] ?? 'pending';
        $this->paymentMethod = $data['payment_method'] ?? 'bank_transfer';
        $this->description = $data['description'] ?? '';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->completedAt = $data['completed_at'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobId(): int
    {
        return $this->jobId;
    }

    public function getEmployerId(): int
    {
        return $this->employerId;
    }

    public function getEmployeeId(): int
    {
        return $this->employeeId;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?string
    {
        return $this->completedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->jobId,
            'employer_id' => $this->employerId,
            'employee_id' => $this->employeeId,
            'amount' => $this->amount,
            'status' => $this->status,
            'payment_method' => $this->paymentMethod,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'completed_at' => $this->completedAt
        ];
    }
}

