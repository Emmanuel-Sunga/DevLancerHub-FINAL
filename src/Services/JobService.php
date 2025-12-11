<?php

namespace App\Services;

use App\Models\Job;

class JobService
{
    private JsonDatabase $db;
    private ValidationService $validator;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['jobs_file']);
        $this->validator = new ValidationService();
    }

    public function createJob(array $data): Job
    {
        $data = $this->validator->validateJob($data);

        $jobData = [
            'employer_id' => (int)$data['employer_id'],
            'title' => trim($data['title']),
            'description' => trim($data['description']),
            'skills_required' => trim($data['skills_required']),
            'budget' => trim($data['budget']),
            'duration' => trim($data['duration']),
            'location' => trim($data['location']),
            'job_type' => trim($data['job_type'] ?? 'Full-time'),
            'status' => 'Open',
            'created_at' => date('Y-m-d H:i:s'),
            'deadline' => !empty($data['deadline']) ? trim($data['deadline']) : null
        ];

        $jobId = $this->db->create($jobData);
        $jobData['id'] = $jobId;

        return new Job($jobData);
    }

    public function getJobById(int $id): ?Job
    {
        $jobData = $this->db->findById($id);
        return $jobData ? new Job($jobData) : null;
    }

    public function getAllJobs(): array
    {
        $jobsData = $this->db->readAll();
        return array_map(fn($data) => new Job($data), $jobsData);
    }

    public function getJobsByEmployer(int $employerId): array
    {
        return array_map(fn($data) => new Job($data), 
            $this->db->findWhere(fn($job) => $job['employer_id'] === $employerId)
        );
    }

    public function getOpenJobs(): array
    {
        return array_map(fn($data) => new Job($data), 
            $this->db->findWhere(fn($job) => $job['status'] === 'Open')
        );
    }

    public function updateJob(int $id, array $data): bool
    {
        return $this->db->update($id, $data);
    }

    public function deleteJob(int $id): bool
    {
        return $this->db->delete($id);
    }
}
