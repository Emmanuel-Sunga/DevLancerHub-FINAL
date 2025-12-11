<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\JobService;
use App\Services\ApplicationService;
use App\Services\PaymentService;
use App\Middleware\SessionMiddleware;

class DashboardController
{
    private AuthService $authService;
    private JobService $jobService;
    private ApplicationService $applicationService;
    private PaymentService $paymentService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->jobService = new JobService();
        $this->applicationService = new ApplicationService();
        $this->paymentService = new PaymentService();
    }

    public function index(): array
    {
        $userId = SessionMiddleware::getUserId();
        $userRole = SessionMiddleware::getUserRole();
        $user = $this->authService->getUserById($userId);

        if (!$user) {
            header('Location: logout.php');
            exit;
        }

        $data = [
            'user' => $user,
            'role' => $userRole
        ];

        if ($userRole === 'employee') {
            $data['jobs'] = $this->jobService->getOpenJobs();
            // Get all employers with their job counts (excluding current user)
            $employers = array_filter(
                $this->authService->getUsersByRole('employer'),
                fn($u) => $u->getId() !== $userId
            );
            $data['employers'] = $this->enrichUsersWithJobInfo($employers);
            // Get all employees (excluding current user) for friend discovery
            $employees = array_filter(
                $this->authService->getUsersByRole('employee'),
                fn($u) => $u->getId() !== $userId
            );
            $data['employees'] = $employees;
            // Get employee's applications
            $data['applications'] = $this->applicationService->getApplicationsByEmployee($userId);
        } else {
            $data['jobs'] = $this->jobService->getJobsByEmployer($userId);
            // Get all employees with their info (excluding current user)
            $employees = array_filter(
                $this->authService->getUsersByRole('employee'),
                fn($u) => $u->getId() !== $userId
            );
            $data['employees'] = $employees;
            // Get all employers with their job counts (excluding current user) for friend discovery
            $employers = array_filter(
                $this->authService->getUsersByRole('employer'),
                fn($u) => $u->getId() !== $userId
            );
            $data['employers'] = $this->enrichUsersWithJobInfo($employers);
            // Get applications for employer's jobs
            $data['applications'] = $this->getApplicationsForEmployer($userId);
        }

        return $data;
    }

    private function enrichUsersWithJobInfo(array $users): array
    {
        return array_map(function($user) {
            $jobs = $this->jobService->getJobsByEmployer($user->getId());
            return [
                'user' => $user,
                'job_count' => count($jobs),
                'jobs' => $jobs
            ];
        }, $users);
    }

    private function getApplicationsForEmployer(int $employerId): array
    {
        $jobs = $this->jobService->getJobsByEmployer($employerId);
        $allApplications = [];
        $jobPayments = [];
        
        // Pre-load all payments for all jobs to avoid repeated queries
        foreach ($jobs as $job) {
            $jobPayments[$job->getId()] = $this->paymentService->getPaymentsByJob($job->getId());
        }
        
        foreach ($jobs as $job) {
            $applications = $this->applicationService->getApplicationsByJob($job->getId());
            foreach ($applications as $application) {
                $employee = $this->authService->getUserById($application->getEmployeeId());
                if (!$employee) continue;
                
                // Check if employee has been paid for this job (using pre-loaded payments)
                $hasBeenPaid = false;
                foreach ($jobPayments[$job->getId()] as $payment) {
                    if ($payment->getEmployeeId() === $application->getEmployeeId() && 
                        $payment->getStatus() === 'completed') {
                        $hasBeenPaid = true;
                        break;
                    }
                }
                
                if ($hasBeenPaid) continue;
                
                $allApplications[] = [
                    'application' => $application,
                    'job' => $job,
                    'employee' => $employee
                ];
            }
        }
        
        // Sort by employee name (alphabetically)
        usort($allApplications, fn($a, $b) => 
            strcasecmp($a['employee']->getFullName(), $b['employee']->getFullName())
        );
        
        // Separate by status
        $grouped = ['pending' => [], 'accepted' => [], 'rejected' => []];
        foreach ($allApplications as $appData) {
            $status = $appData['application']->getStatus();
            $grouped[$status === 'pending' ? 'pending' : ($status === 'accepted' ? 'accepted' : 'rejected')][] = $appData;
        }
        
        return [
            'all' => $allApplications,
            'pending' => $grouped['pending'],
            'accepted' => $grouped['accepted'],
            'rejected' => $grouped['rejected']
        ];
    }
}
