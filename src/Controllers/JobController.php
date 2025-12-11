<?php

namespace App\Controllers;

use App\Services\JobService;
use App\Services\AuthService;
use App\Middleware\SessionMiddleware;
use App\Exceptions\ValidationException;

class JobController
{
    private JobService $jobService;
    private AuthService $authService;

    public function __construct()
    {
        $this->jobService = new JobService();
        $this->authService = new AuthService();
    }

    private function requireEmployerRole(): void
    {
        $userId = SessionMiddleware::getUserId();
        if (!$userId || ($user = $this->authService->getUserById($userId)) === null || $user->getRole() !== 'employer') {
            throw new \Exception($userId ? 'Unauthorized: Only employers can perform this action' : 'Unauthorized: Please login');
        }
    }

    public function create(): array
    {
        try {
            $this->requireEmployerRole();

            $data = $_POST;
            $data['employer_id'] = SessionMiddleware::getUserId();
            
            $job = $this->jobService->createJob($data);

            SessionMiddleware::setFlash('success', 'Job posted successfully!');

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

    public function update(): array
    {
        try {
            $this->requireEmployerRole();

            $jobId = (int)$_POST['job_id'];
            $job = $this->jobService->getJobById($jobId);

            if (!$job || $job->getEmployerId() !== SessionMiddleware::getUserId()) {
                throw new \Exception('Unauthorized: You can only update your own jobs');
            }

            unset($_POST['job_id']);
            $this->jobService->updateJob($jobId, $_POST);

            SessionMiddleware::setFlash('success', 'Job updated successfully!');

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

    public function delete(): array
    {
        try {
            $this->requireEmployerRole();

            $jobId = (int)$_POST['job_id'];
            $job = $this->jobService->getJobById($jobId);

            if (!$job || $job->getEmployerId() !== SessionMiddleware::getUserId()) {
                throw new \Exception('Unauthorized: You can only delete your own jobs');
            }

            $this->jobService->deleteJob($jobId);

            SessionMiddleware::setFlash('success', 'Job deleted successfully!');

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

    public function getAll(): array
    {
        return [
            'success' => true,
            'jobs' => array_map(fn($job) => $job->toArray(), $this->jobService->getAllJobs())
        ];
    }
}
