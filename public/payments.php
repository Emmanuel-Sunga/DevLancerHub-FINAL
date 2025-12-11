<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\PaymentController;
use App\Services\JobService;
use App\Services\AuthService;
use App\Services\ApplicationService;

SessionMiddleware::start();
AuthMiddleware::requireAuth();

$paymentController = new PaymentController();
$userId = SessionMiddleware::getUserId();
$userRole = SessionMiddleware::getUserRole();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create_payment':
            $result = $paymentController->create();
            break;
        case 'complete_payment':
            $result = $paymentController->complete();
            break;
        case 'cancel_payment':
            $result = $paymentController->cancel();
            break;
    }
    
    if (isset($result) && $result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } elseif (isset($result) && !$result['success']) {
        $errorMsg = isset($result['errors']['general']) ? $result['errors']['general'] : 'An error occurred';
        SessionMiddleware::setFlash('error', $errorMsg);
    }
}

$data = $paymentController->index();
$payments = $data['payments'];
$totalEarnings = $data['total_earnings'];
$totalSpent = $data['total_spent'];

$jobService = new JobService();
$authService = new AuthService();
$applicationService = new ApplicationService();
$user = $authService->getUserById($userId);

$flashMessage = SessionMiddleware::getFlash('success');
$errorMessage = SessionMiddleware::getFlash('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">
                    <h1>DevLancerHub</h1>
                    <p class="tagline">Payments</p>
                </a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="friends.php" class="nav-link">Friends</a>
                <a href="payments.php" class="nav-link active">Payments</a>
                <a href="profile.php?id=<?= $userId ?>" class="nav-link">My Profile</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashMessage) ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="alert alert-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>Payment Management</h2>
                <?php if ($userRole === 'employee'): ?>
                    <p class="subtitle">Total Earnings: $<?= number_format($totalEarnings, 2) ?></p>
                <?php else: ?>
                    <p class="subtitle">Total Spent: $<?= number_format($totalSpent, 2) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($userRole === 'employer'): ?>
                <div class="section">
                    <h3>Create New Payment</h3>
                    <form method="POST" class="job-form" id="paymentForm">
                        <input type="hidden" name="action" value="create_payment">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="job_id">Job *</label>
                                <select id="job_id" name="job_id" required onchange="updateEmployeeList()">
                                    <option value="">Select a job</option>
                                    <?php 
                                    $jobs = $jobService->getJobsByEmployer($userId);
                                    foreach ($jobs as $job): ?>
                                        <option value="<?= $job->getId() ?>"><?= htmlspecialchars($job->getTitle()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="employee_id">Employee *</label>
                                <select id="employee_id" name="employee_id" required>
                                    <option value="">Select a job first</option>
                                </select>
                                <small class="text-muted">Only employees who have applied and been accepted are shown</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="amount">Amount ($) *</label>
                                <input type="number" id="amount" name="amount" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label for="payment_method">Payment Method *</label>
                                <select id="payment_method" name="payment_method" required>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="paypal">PayPal</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="crypto">Cryptocurrency</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Payment</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="section">
                <h3>Payment History (<?= count($payments) ?>)</h3>
                <?php if (empty($payments)): ?>
                    <p class="empty-state">No payments yet.</p>
                <?php else: ?>
                    <div class="payments-list">
                        <?php foreach ($payments as $item): 
                            $payment = $item['payment'];
                            $job = $item['job'];
                            $isEmployer = $payment->getEmployerId() === $userId;
                            $otherUser = $isEmployer ? $authService->getUserById($payment->getEmployeeId()) : $authService->getUserById($payment->getEmployerId());
                        ?>
                            <div class="payment-card">
                                <div class="payment-header">
                                    <div>
                                        <h4><?= $job ? htmlspecialchars($job->getTitle()) : 'Job #' . $payment->getJobId() ?></h4>
                                        <p>
                                            <?= $isEmployer ? 'To: ' : 'From: ' ?>
                                            <?= $otherUser ? htmlspecialchars($otherUser->getFullName()) : 'User #' . ($isEmployer ? $payment->getEmployeeId() : $payment->getEmployerId()) ?>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <strong style="font-size: 1.2rem;">$<?= number_format($payment->getAmount(), 2) ?></strong>
                                        <span class="badge <?= $payment->getStatus() === 'completed' ? 'badge-success' : ($payment->getStatus() === 'pending' ? 'badge' : 'badge-danger') ?>">
                                            <?= ucfirst($payment->getStatus()) ?>
                                        </span>
                                    </div>
                                </div>
                                <p><strong>Payment Method:</strong> <?= ucfirst(str_replace('_', ' ', $payment->getPaymentMethod())) ?></p>
                                <?php if ($payment->getDescription()): ?>
                                    <p><strong>Description:</strong> <?= htmlspecialchars($payment->getDescription()) ?></p>
                                <?php endif; ?>
                                <p class="text-muted">Created: <?= date('M d, Y H:i', strtotime($payment->getCreatedAt())) ?></p>
                                <?php if ($payment->getCompletedAt()): ?>
                                    <p class="text-muted">Completed: <?= date('M d, Y H:i', strtotime($payment->getCompletedAt())) ?></p>
                                <?php endif; ?>
                                
                                <?php if ($userRole === 'employer' && $payment->getStatus() === 'pending'): ?>
                                    <div class="card-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="complete_payment">
                                            <input type="hidden" name="payment_id" value="<?= $payment->getId() ?>">
                                            <button type="submit" class="btn btn-success btn-small">Mark as Completed</button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="cancel_payment">
                                            <input type="hidden" name="payment_id" value="<?= $payment->getId() ?>">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Cancel this payment?')">Cancel</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($userRole === 'employer'): ?>
    <script>
        const jobApplications = <?= json_encode(array_map(function($job) use ($applicationService, $userId) {
            $applications = $applicationService->getApplicationsByJob($job->getId());
            $acceptedApplications = array_filter($applications, fn($app) => $app->getStatus() === 'accepted');
            return [
                'job_id' => $job->getId(),
                'applications' => array_map(function($app) {
                    return [
                        'application_id' => $app->getId(),
                        'employee_id' => $app->getEmployeeId(),
                        'status' => $app->getStatus()
                    ];
                }, $acceptedApplications)
            ];
        }, $jobs)) ?>;
        
        const employeeData = <?= json_encode(array_map(function($job) use ($applicationService, $authService) {
            $applications = $applicationService->getApplicationsByJob($job->getId());
            $acceptedApplications = array_filter($applications, fn($app) => $app->getStatus() === 'accepted');
            $employees = [];
            foreach ($acceptedApplications as $app) {
                $employee = $authService->getUserById($app->getEmployeeId());
                if ($employee) {
                    $employees[] = [
                        'id' => $employee->getId(),
                        'name' => $employee->getFullName(),
                        'email' => $employee->getEmail()
                    ];
                }
            }
            return [
                'job_id' => $job->getId(),
                'employees' => $employees
            ];
        }, $jobs)) ?>;

        function updateEmployeeList() {
            const jobId = parseInt(document.getElementById('job_id').value);
            const employeeSelect = document.getElementById('employee_id');
            
            employeeSelect.innerHTML = '<option value="">Select an employee</option>';
            
            if (!jobId) {
                employeeSelect.innerHTML = '<option value="">Select a job first</option>';
                return;
            }
            
            const jobData = employeeData.find(j => j.job_id === jobId);
            if (jobData && jobData.employees.length > 0) {
                jobData.employees.forEach(employee => {
                    const option = document.createElement('option');
                    option.value = employee.id;
                    option.textContent = employee.name + ' (' + employee.email + ')';
                    employeeSelect.appendChild(option);
                });
            } else {
                employeeSelect.innerHTML = '<option value="">No accepted applicants for this job</option>';
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>

