<?php

namespace App\Models;

class Job
{
    private ?int $id;
    private int $employerId;
    private string $title;
    private string $description;
    private string $skillsRequired;
    private string $budget;
    private string $duration;
    private string $location;
    private string $jobType;
    private string $status;
    private string $createdAt;
    private ?string $deadline;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->employerId = $data['employer_id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->skillsRequired = $data['skills_required'] ?? '';
        $this->budget = $data['budget'] ?? '';
        $this->duration = $data['duration'] ?? '';
        $this->location = $data['location'] ?? '';
        $this->jobType = $data['job_type'] ?? 'Full-time';
        $this->status = $data['status'] ?? 'Open';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->deadline = $data['deadline'] ?? null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmployerId(): int
    {
        return $this->employerId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSkillsRequired(): string
    {
        return $this->skillsRequired;
    }

    public function getBudget(): string
    {
        return $this->budget;
    }

    public function getDuration(): string
    {
        return $this->duration;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getJobType(): string
    {
        return $this->jobType;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getDeadline(): ?string
    {
        return $this->deadline;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employer_id' => $this->employerId,
            'title' => $this->title,
            'description' => $this->description,
            'skills_required' => $this->skillsRequired,
            'budget' => $this->budget,
            'duration' => $this->duration,
            'location' => $this->location,
            'job_type' => $this->jobType,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'deadline' => $this->deadline
        ];
    }
}
