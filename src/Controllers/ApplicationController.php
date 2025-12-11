<?php

namespace App\Controllers;

use App\Services\ApplicationService;
use App\Services\AuthService;
use App\Services\JobService;
use App\Middleware\SessionMiddleware;
use App\Exceptions\ValidationException;

class ApplicationController
{
    private ApplicationService $applicationService;
    private AuthService $authService;
    private JobService $jobService;

    public function __construct()
    {
        $this->applicationService = new ApplicationService();
        $this->authService = new AuthService();
        $this->jobService = new JobService();
    }

    public function create(): array
    {
        try {
            $userId = SessionMiddleware::getUserId();
            if (!$userId || ($user = $this->authService->getUserById($userId)) === null || $user->getRole() !== 'employee') {
                throw new \Exception($userId ? 'Unauthorized: Only employees can apply for jobs' : 'Unauthorized: Please login');
            }

            $jobId = (int)$_POST['job_id'];
            $job = $this->jobService->getJobById($jobId);
            
            if (!$job) {
                throw new \Exception('Job not found');
            }

            if ($job->getStatus() !== 'Open') {
                throw new \Exception('This job is no longer accepting applications');
            }

            $data = [
                'job_id' => $jobId,
                'employee_id' => $userId,
                'cover_letter' => $_POST['cover_letter'] ?? ''
            ];

            $application = $this->applicationService->createApplication($data);

            SessionMiddleware::setFlash('success', 'Application submitted successfully!');

            return [
                'success' => true,
                'redirect' => 'dashboard.php'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->getErrors()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function updateStatus(): array
    {
        try {
            $userId = SessionMiddleware::getUserId();
            if (!$userId || ($user = $this->authService->getUserById($userId)) === null || $user->getRole() !== 'employer') {
                throw new \Exception($userId ? 'Unauthorized: Only employers can manage applications' : 'Unauthorized: Please login');
            }

            $applicationId = (int)$_POST['application_id'];
            $status = $_POST['status'];

            $application = $this->applicationService->getApplicationById($applicationId);
            if (!$application) {
                throw new \Exception('Application not found');
            }

            $job = $this->jobService->getJobById($application->getJobId());
            if (!$job || $job->getEmployerId() !== $userId) {
                throw new \Exception('Unauthorized: You can only manage applications for your own jobs');
            }

            $this->applicationService->updateApplicationStatus($applicationId, $status);

            $statusMessages = ['accepted' => 'Application accepted successfully!', 'rejected' => 'Application rejected.'];
            SessionMiddleware::setFlash('success', $statusMessages[$status] ?? 'Application status updated.');

            return [
                'success' => true,
                'redirect' => 'dashboard.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }
}

