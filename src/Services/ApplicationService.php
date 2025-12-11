<?php

namespace App\Services;

use App\Models\Application;

class ApplicationService
{
    private JsonDatabase $db;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['applications_file']);
    }

    public function createApplication(array $data): Application
    {
        // Check if application already exists
        $jobId = (int)$data['job_id'];
        $employeeId = (int)$data['employee_id'];
        $existing = $this->db->findWhere(fn($app) => $app['job_id'] === $jobId && $app['employee_id'] === $employeeId);

        if (!empty($existing)) {
            throw new \Exception('You have already applied for this job');
        }

        $applicationData = [
            'job_id' => (int)$data['job_id'],
            'employee_id' => (int)$data['employee_id'],
            'cover_letter' => trim($data['cover_letter'] ?? ''),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $id = $this->db->create($applicationData);
        $applicationData['id'] = $id;

        return new Application($applicationData);
    }

    public function getApplicationById(int $id): ?Application
    {
        $data = $this->db->findById($id);
        return $data ? new Application($data) : null;
    }

    public function getApplicationsByJob(int $jobId): array
    {
        return array_map(fn($data) => new Application($data), 
            $this->db->findWhere(fn($app) => $app['job_id'] === $jobId)
        );
    }

    public function getApplicationsByEmployee(int $employeeId): array
    {
        return array_map(fn($data) => new Application($data), 
            $this->db->findWhere(fn($app) => $app['employee_id'] === $employeeId)
        );
    }

    public function updateApplicationStatus(int $id, string $status): bool
    {
        $validStatuses = ['pending', 'accepted', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception('Invalid application status');
        }

        return $this->db->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function hasApplied(int $jobId, int $employeeId): bool
    {
        return !empty($this->db->findWhere(fn($app) => $app['job_id'] === $jobId && $app['employee_id'] === $employeeId));
    }
}

