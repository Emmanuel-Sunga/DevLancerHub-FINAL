<?php

namespace App\Controllers;

use App\Services\PaymentService;
use App\Services\JobService;
use App\Middleware\SessionMiddleware;

class PaymentController
{
    private PaymentService $paymentService;
    private JobService $jobService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->jobService = new JobService();
    }

    public function create(): array
    {
        try {
            $userId = SessionMiddleware::getUserId();
            $jobId = (int)$_POST['job_id'];
            $job = $this->jobService->getJobById($jobId);

            if (!$job || $job->getEmployerId() !== $userId) {
                return [
                    'success' => false,
                    'errors' => ['general' => 'Job not found or unauthorized']
                ];
            }

            $data = [
                'job_id' => $jobId,
                'employer_id' => $userId,
                'employee_id' => (int)$_POST['employee_id'],
                'amount' => (float)$_POST['amount'],
                'payment_method' => $_POST['payment_method'] ?? 'bank_transfer',
                'description' => $_POST['description'] ?? ''
            ];

            $this->paymentService->createPayment($data);
            SessionMiddleware::setFlash('success', 'Payment created successfully!');

            return [
                'success' => true,
                'redirect' => 'payments.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function complete(): array
    {
        try {
            $paymentId = (int)$_POST['payment_id'];
            $this->paymentService->completePayment($paymentId);
            SessionMiddleware::setFlash('success', 'Payment marked as completed!');

            return [
                'success' => true,
                'redirect' => 'payments.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function cancel(): array
    {
        try {
            $paymentId = (int)$_POST['payment_id'];
            $this->paymentService->cancelPayment($paymentId);
            SessionMiddleware::setFlash('success', 'Payment cancelled.');

            return [
                'success' => true,
                'redirect' => 'payments.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function index(): array
    {
        $userId = SessionMiddleware::getUserId();
        $payments = $this->paymentService->getPaymentsByUser($userId);
        
        // Get job details for each payment (batch load jobs to avoid repeated queries)
        $jobIds = array_unique(array_map(fn($p) => $p->getJobId(), $payments));
        $jobs = [];
        foreach ($jobIds as $jobId) {
            $jobs[$jobId] = $this->jobService->getJobById($jobId);
        }
        
        $paymentsWithDetails = array_map(fn($payment) => [
            'payment' => $payment,
            'job' => $jobs[$payment->getJobId()] ?? null
        ], $payments);

        // Sort by created_at descending
        usort($paymentsWithDetails, fn($a, $b) => 
            strtotime($b['payment']->getCreatedAt()) - strtotime($a['payment']->getCreatedAt())
        );

        return [
            'payments' => $paymentsWithDetails,
            'total_earnings' => $this->paymentService->getTotalEarnings($userId),
            'total_spent' => $this->paymentService->getTotalSpent($userId)
        ];
    }
}

