<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\DashboardController;
use App\Controllers\JobController;
use App\Controllers\ApplicationController;
use App\Services\ApplicationService;
use App\Services\FriendService;

SessionMiddleware::start();
AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $jobController = new JobController();
    $applicationController = new ApplicationController();
    
    switch ($_POST['action']) {
        case 'create_job':
            $result = $jobController->create();
            break;
        case 'update_job':
            $result = $jobController->update();
            break;
        case 'delete_job':
            $result = $jobController->delete();
            break;
        case 'apply_job':
            $result = $applicationController->create();
            break;
        case 'update_application_status':
            $result = $applicationController->updateStatus();
            break;
    }
    
    if (isset($result) && $result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    } elseif (isset($result) && !$result['success']) {
        // Store all validation errors in session
        if (isset($result['errors'])) {
            if (isset($result['errors']['general'])) {
                SessionMiddleware::setFlash('error', $result['errors']['general']);
            } else {
                // Combine all field errors into a single message
                $errorMessages = [];
                foreach ($result['errors'] as $field => $message) {
                    $errorMessages[] = ucfirst(str_replace('_', ' ', $field)) . ': ' . $message;
                }
                SessionMiddleware::setFlash('error', implode('<br>', $errorMessages));
            }
            // Store field-specific errors for display
            SessionMiddleware::set('validation_errors', $result['errors']);
        } else {
            SessionMiddleware::setFlash('error', 'An error occurred');
        }
    }
}

$controller = new DashboardController();
$data = $controller->index();
$user = $data['user'];
$userId = $user->getId();
$role = $data['role'];
$jobs = $data['jobs'];
$employees = $data['employees'] ?? [];
$employers = $data['employers'] ?? [];
$applicationsData = $data['applications'] ?? [];

// Handle different formats: employer returns structured array, employee returns simple array
if ($role === 'employer' && isset($applicationsData['all'])) {
    // Employer format: structured array with pending/accepted/rejected
    $applications = $applicationsData['all'];
    $pendingApplications = $applicationsData['pending'] ?? [];
    $acceptedApplications = $applicationsData['accepted'] ?? [];
    $rejectedApplications = $applicationsData['rejected'] ?? [];
} else {
    // Employee format: simple array of Application objects, or fallback for employer
    $applications = is_array($applicationsData) && isset($applicationsData[0]) && is_object($applicationsData[0]) 
        ? $applicationsData 
        : (isset($applicationsData['all']) ? $applicationsData['all'] : $applicationsData);
    
    if ($role === 'employer' && is_array($applications) && isset($applications[0]) && isset($applications[0]['application'])) {
        // Old employer format - convert to new format
        $pendingApplications = array_filter($applications, fn($app) => $app['application']->getStatus() === 'pending');
        $acceptedApplications = array_filter($applications, fn($app) => $app['application']->getStatus() === 'accepted');
        $rejectedApplications = array_filter($applications, fn($app) => $app['application']->getStatus() === 'rejected');
    } else {
        $pendingApplications = [];
        $acceptedApplications = [];
        $rejectedApplications = [];
    }
}

$flashMessage = SessionMiddleware::getFlash('success');
$errorMessage = SessionMiddleware::getFlash('error');
$validationErrors = SessionMiddleware::get('validation_errors', []);
// Clear validation errors after retrieving
if (!empty($validationErrors)) {
    SessionMiddleware::remove('validation_errors');
}
$applicationService = new ApplicationService();
$friendService = new FriendService();

// Helper function to check friend status
function getFriendStatus($friendService, $currentUserId, $targetUserId) {
    // Return 'none' if either user ID is invalid
    if ($currentUserId === null || $targetUserId === null) {
        return 'none';
    }
    
    if ($friendService->isFriend($currentUserId, $targetUserId)) {
        return 'friend';
    }
    $sentRequests = $friendService->getSentRequests($currentUserId);
    foreach ($sentRequests as $request) {
        if ($request->getFriendId() === $targetUserId) {
            return 'pending';
        }
    }
    return 'none';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard</title>
	<link rel="stylesheet" href="css/style.css">
	<script src="js/dashboard.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
				<a href="dashboard.php">
					<h1>DevLancerHub</h1>
					<p class="tagline">Dashboard</p>
                </a>
            </div>
            <div class="nav-links">
				<a href="dashboard.php" class="nav-link active">Dashboard</a>
				<a href="messages.php" class="nav-link">Messages</a>
				<a href="friends.php" class="nav-link">Friends</a>
				<a href="payments.php" class="nav-link">Payments</a>
				<a href="profile.php?id=<?= $user->getId() ?>" class="nav-link">My Profile</a>
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
                <div class="alert alert-error"><?= $errorMessage ?></div>
            <?php endif; ?>

            <div class="dashboard-header">
                <h2>Welcome, <?= htmlspecialchars($user->getFirstName()) ?>!</h2>
                <p class="subtitle">You are logged in as <?= $role === 'employee' ? 'Freelancer' : 'Client' ?></p>
            </div>

            <?php if ($role === 'employer'): ?>
                <div class="section">
                    <div class="section-header">
                        <h3>Post a New Job</h3>
                    </div>
					<form method="POST" action="dashboard.php" class="job-form" id="jobForm">
                        <input type="hidden" name="action" value="create_job">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="title">Job Title *</label>
                                <input type="text" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                                <?php if (isset($validationErrors['title'])): ?>
                                    <span class="error-message"><?= htmlspecialchars($validationErrors['title']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="job_type">Job Type *</label>
                                <select id="job_type" name="job_type" required>
                                    <option value="Full-time" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'Full-time') ? 'selected' : '' ?>>Full-time</option>
                                    <option value="Part-time" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'Part-time') ? 'selected' : '' ?>>Part-time</option>
                                    <option value="Contract" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'Contract') ? 'selected' : '' ?>>Contract</option>
                                    <option value="Freelance" <?= (isset($_POST['job_type']) && $_POST['job_type'] === 'Freelance') ? 'selected' : '' ?>>Freelance</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="4" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                            <?php if (isset($validationErrors['description'])): ?>
                                <span class="error-message"><?= htmlspecialchars($validationErrors['description']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="skills_required">Required Skills *</label>
                                <input type="text" id="skills_required" name="skills_required" value="<?= htmlspecialchars($_POST['skills_required'] ?? '') ?>" placeholder="e.g., PHP, MySQL, JavaScript" required>
                                <?php if (isset($validationErrors['skills_required'])): ?>
                                    <span class="error-message"><?= htmlspecialchars($validationErrors['skills_required']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="budget">Budget *</label>
                                <input type="text" id="budget" name="budget" value="<?= htmlspecialchars($_POST['budget'] ?? '') ?>" placeholder="e.g., $5000 - $10000" required>
                                <?php if (isset($validationErrors['budget'])): ?>
                                    <span class="error-message"><?= htmlspecialchars($validationErrors['budget']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="duration">Duration *</label>
                                <input type="text" id="duration" name="duration" value="<?= htmlspecialchars($_POST['duration'] ?? '') ?>" placeholder="e.g., 3 months" required>
                                <?php if (isset($validationErrors['duration'])): ?>
                                    <span class="error-message"><?= htmlspecialchars($validationErrors['duration']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label for="location">Location *</label>
                                <input type="text" id="location" name="location" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="e.g., Remote, New York" required>
                                <?php if (isset($validationErrors['location'])): ?>
                                    <span class="error-message"><?= htmlspecialchars($validationErrors['location']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="deadline">Deadline (Optional)</label>
                            <input type="date" id="deadline" name="deadline" value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Post Job</button>
                    </form>
                </div>

                <div class="section">
                    <h3>My Job Postings (<?= count($jobs) ?>)</h3>
                    <div class="jobs-grid">
                        <?php if (empty($jobs)): ?>
                            <p class="empty-state">No jobs posted yet. Create your first job posting above!</p>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <div class="job-card">
                                    <h4><?= htmlspecialchars($job->getTitle()) ?></h4>
                                    <p class="job-meta">
                                        <span class="badge"><?= htmlspecialchars($job->getJobType()) ?></span>
                                        <span class="badge badge-success"><?= htmlspecialchars($job->getStatus()) ?></span>
                                    </p>
                                    <p><?= htmlspecialchars(substr($job->getDescription(), 0, 150)) ?>...</p>
                                    <p><strong>Skills:</strong> <?= htmlspecialchars($job->getSkillsRequired()) ?></p>
                                    <p><strong>Budget:</strong> <?= htmlspecialchars($job->getBudget()) ?></p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($job->getLocation()) ?></p>
                                    <?php if ($job->getDeadline()): ?>
                                        <p><strong>Deadline:</strong> <?= date('M d, Y', strtotime($job->getDeadline())) ?></p>
                                    <?php endif; ?>
                                    <div class="job-actions">
                                        <button type="button" class="btn btn-outline btn-small" onclick="openEditJobModal(<?= $job->getId() ?>)">Edit</button>
                                        <form method="POST" action="dashboard.php" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_job">
                                            <input type="hidden" name="job_id" value="<?= $job->getId() ?>">
                                            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to delete this job?')">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Applications Section -->
                <div class="section">
                    <h3>Pending Applications (<?= count($pendingApplications) ?>)</h3>
                    <p class="section-description">Review and respond to new job applications</p>
                    <?php if (empty($pendingApplications)): ?>
                        <p class="empty-state">No pending applications at the moment.</p>
                    <?php else: ?>
                    <div class="applications-list">
                        <?php foreach ($pendingApplications as $appData): 
                            $application = $appData['application'];
                            $job = $appData['job'];
                            $employee = $appData['employee'];
                            if (!$employee) continue;
                        ?>
                            <div class="application-card">
                                <div class="application-header">
                                    <div>
                                        <h4><?= htmlspecialchars($employee->getFullName()) ?></h4>
                                        <p class="application-job-title">Applied for: <strong><?= htmlspecialchars($job->getTitle()) ?></strong></p>
                                    </div>
                                    <span class="badge badge-info">Pending</span>
                                </div>
                                <div class="application-details">
                                    <p><strong>Skills:</strong> <?= htmlspecialchars($employee->getSkills()) ?></p>
                                    <p><strong>Experience:</strong> <?= htmlspecialchars($employee->getExperienceYears()) ?> years</p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($employee->getLocation()) ?></p>
                                    <?php if ($application->getCoverLetter()): ?>
                                        <p><strong>Cover Letter:</strong></p>
                                        <p class="cover-letter"><?= nl2br(htmlspecialchars($application->getCoverLetter())) ?></p>
                                    <?php endif; ?>
                                    <p class="application-date">Applied: <?= date('M d, Y H:i', strtotime($application->getCreatedAt())) ?></p>
                                </div>
                                <div class="application-actions">
                                    <form method="POST" action="dashboard.php" style="display: inline;">
                                        <input type="hidden" name="action" value="update_application_status">
                                        <input type="hidden" name="application_id" value="<?= $application->getId() ?>">
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-success btn-small">Accept</button>
                                    </form>
                                    <form method="POST" action="dashboard.php" style="display: inline;">
                                        <input type="hidden" name="action" value="update_application_status">
                                        <input type="hidden" name="application_id" value="<?= $application->getId() ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Are you sure you want to reject this application?')">Reject</button>
                                    </form>
                                    <a href="profile.php?id=<?= $employee->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Accepted Applications Section -->
                <div class="section">
                    <h3>Accepted Applications (<?= count($acceptedApplications) ?>)</h3>
                    <p class="section-description">Manage accepted applications and process payments</p>
                    <?php if (empty($acceptedApplications)): ?>
                        <p class="empty-state">No accepted applications. Accepted applications will appear here after you accept them.</p>
                    <?php else: ?>
                    <div class="applications-list">
                        <?php foreach ($acceptedApplications as $appData): 
                            $application = $appData['application'];
                            $job = $appData['job'];
                            $employee = $appData['employee'];
                            if (!$employee) continue;
                        ?>
                            <div class="application-card">
                                <div class="application-header">
                                    <div>
                                        <h4><?= htmlspecialchars($employee->getFullName()) ?></h4>
                                        <p class="application-job-title">Applied for: <strong><?= htmlspecialchars($job->getTitle()) ?></strong></p>
                                    </div>
                                    <span class="badge badge-success">Accepted</span>
                                </div>
                                <div class="application-details">
                                    <p><strong>Skills:</strong> <?= htmlspecialchars($employee->getSkills()) ?></p>
                                    <p><strong>Experience:</strong> <?= htmlspecialchars($employee->getExperienceYears()) ?> years</p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($employee->getLocation()) ?></p>
                                    <?php if ($application->getCoverLetter()): ?>
                                        <p><strong>Cover Letter:</strong></p>
                                        <p class="cover-letter"><?= nl2br(htmlspecialchars($application->getCoverLetter())) ?></p>
                                    <?php endif; ?>
                                    <p class="application-date">Applied: <?= date('M d, Y H:i', strtotime($application->getCreatedAt())) ?></p>
                                </div>
                                <div class="application-actions">
                                    <button type="button" class="btn btn-primary btn-small" onclick="openPaymentModal(<?= $job->getId() ?>, <?= $employee->getId() ?>, '<?= htmlspecialchars($employee->getFullName(), ENT_QUOTES) ?>', '<?= htmlspecialchars($job->getTitle(), ENT_QUOTES) ?>')">Pay Employee</button>
                                    <a href="profile.php?id=<?= $employee->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="section">
                    <h3>Browse Freelancers (<?= count($employees) ?>)</h3>
                    <div class="employees-grid">
                        <?php if (empty($employees)): ?>
                            <p class="empty-state">No freelancers available yet.</p>
                        <?php else: ?>
                            <?php foreach ($employees as $employee): 
                                $friendStatus = getFriendStatus($friendService, $userId, $employee->getId());
                            ?>
                                <div class="employee-card">
                                    <h4><?= htmlspecialchars($employee->getFullName()) ?></h4>
                                    <p class="employee-meta">
                                        <span class="badge badge-primary">Employee</span>
                                        <span class="badge"><?= htmlspecialchars($employee->getExperienceYears()) ?> years exp</span>
                                        <span class="badge badge-info">📍 <?= htmlspecialchars($employee->getLocation()) ?></span>
                                    </p>
                                    <p><strong>Skills:</strong> <?= htmlspecialchars($employee->getSkills()) ?></p>
                                    <?php if ($employee->getBio()): ?>
                                        <p class="bio-preview"><?= htmlspecialchars(substr($employee->getBio(), 0, 100)) ?>...</p>
                                    <?php endif; ?>
                                    <div class="card-actions">
                                        <a href="profile.php?id=<?= $employee->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                                        <?php if ($friendStatus === 'none'): ?>
                                            <form method="POST" action="friends.php" style="display: inline;">
                                                <input type="hidden" name="action" value="send_request">
                                                <input type="hidden" name="friend_id" value="<?= $employee->getId() ?>">
                                                <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                                            </form>
                                        <?php elseif ($friendStatus === 'pending'): ?>
                                            <span class="badge">Request Sent</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Friends</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($employers)): ?>
                <div class="section">
                    <h3>Browse Employers & Clients (<?= count($employers) ?>)</h3>
                    <p class="section-description">Explore employers and see the jobs they've posted</p>
                    <div class="employers-grid">
                        <?php foreach ($employers as $employerData): 
                            $employer = $employerData['user'];
                            $employerJobs = $employerData['jobs'];
                            $jobCount = $employerData['job_count'];
                            $friendStatus = getFriendStatus($friendService, $userId, $employer->getId());
                        ?>
                            <div class="employer-card">
                                <div class="employer-header">
                                    <h4><?= htmlspecialchars($employer->getFullName()) ?></h4>
                                    <span class="badge badge-success">Employer</span>
                                </div>
                                <p class="employer-meta">
                                    <span class="badge badge-primary"><?= $jobCount ?> <?= $jobCount === 1 ? 'Job' : 'Jobs' ?></span>
                                    <span class="badge badge-info">📍 <?= htmlspecialchars($employer->getLocation()) ?></span>
                                </p>
                                <?php if ($employer->getBio()): ?>
                                    <p class="bio-preview"><?= htmlspecialchars(substr($employer->getBio(), 0, 120)) ?>...</p>
                                <?php endif; ?>
                                
                                <?php if (!empty($employerJobs)): ?>
                                    <div class="employer-jobs-preview">
                                        <strong>Recent Jobs:</strong>
                                        <ul class="jobs-list-mini">
                                            <?php foreach (array_slice($employerJobs, 0, 3) as $job): ?>
                                                <li>
                                                    <span class="job-title-mini"><?= htmlspecialchars($job->getTitle()) ?></span>
                                                    <span class="badge badge-small"><?= htmlspecialchars($job->getJobType()) ?></span>
                                                    <span class="badge badge-small badge-success"><?= htmlspecialchars($job->getStatus()) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                            <?php if (count($employerJobs) > 3): ?>
                                                <li class="more-jobs">+<?= count($employerJobs) - 3 ?> more job<?= count($employerJobs) - 3 > 1 ? 's' : '' ?></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="card-actions">
                                    <a href="profile.php?id=<?= $employer->getId() ?>" class="btn btn-outline btn-small">View Profile & Jobs</a>
                                    <?php if ($friendStatus === 'none'): ?>
                                        <form method="POST" action="friends.php" style="display: inline;">
                                            <input type="hidden" name="action" value="send_request">
                                            <input type="hidden" name="friend_id" value="<?= $employer->getId() ?>">
                                            <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                                        </form>
                                    <?php elseif ($friendStatus === 'pending'): ?>
                                        <span class="badge">Request Sent</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Friends</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="section">
                    <div class="section-header">
                        <h3>Available Jobs (<?= count($jobs) ?>)</h3>
                        <div class="search-container">
                            <input type="text" id="jobSearch" placeholder="Search jobs by title, skills, or location..." class="search-input">
                        </div>
                    </div>
                    <div class="filter-container">
                        <select id="jobTypeFilter" class="filter-select">
                            <option value="">All Job Types</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                        <select id="locationFilter" class="filter-select">
                            <option value="">All Locations</option>
                            <?php
                            $locations = array_unique(array_map(fn($job) => $job->getLocation(), $jobs));
                            foreach ($locations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="jobs-grid" id="jobsGrid">
                        <?php if (empty($jobs)): ?>
                            <p class="empty-state">No jobs available at the moment. Check back later!</p>
                        <?php else: ?>
                            <?php foreach ($jobs as $job): ?>
                                <div class="job-card">
                                    <h4><?= htmlspecialchars($job->getTitle()) ?></h4>
                                    <p class="job-meta">
                                        <span class="badge"><?= htmlspecialchars($job->getJobType()) ?></span>
                                        <span class="badge badge-success"><?= htmlspecialchars($job->getStatus()) ?></span>
                                    </p>
                                    <p><?= htmlspecialchars($job->getDescription()) ?></p>
                                    <p><strong>Skills Required:</strong> <?= htmlspecialchars($job->getSkillsRequired()) ?></p>
                                    <p><strong>Budget:</strong> <?= htmlspecialchars($job->getBudget()) ?></p>
                                    <p><strong>Duration:</strong> <?= htmlspecialchars($job->getDuration()) ?></p>
                                    <p><strong>Location:</strong> <?= htmlspecialchars($job->getLocation()) ?></p>
                                    <?php if ($job->getDeadline()): ?>
                                        <p><strong>Deadline:</strong> <?= date('M d, Y', strtotime($job->getDeadline())) ?></p>
                                    <?php endif; ?>
                                    <p class="job-date">Posted: <?= date('M d, Y', strtotime($job->getCreatedAt())) ?></p>
                                    <div class="job-actions">
                                        <?php if ($applicationService->hasApplied($job->getId(), $user->getId())): ?>
                                            <span class="badge badge-info">Already Applied</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-primary btn-small" onclick="openApplyModal(<?= $job->getId() ?>, '<?= htmlspecialchars($job->getTitle(), ENT_QUOTES) ?>')">Apply Now</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section">
                    <h3>Browse Employers & Clients (<?= count($employers) ?>)</h3>
                    <p class="section-description">Explore employers and see the jobs they've posted</p>
                    <div class="employers-grid">
                        <?php if (empty($employers)): ?>
                            <p class="empty-state">No employers available yet.</p>
                        <?php else: ?>
                            <?php foreach ($employers as $employerData): 
                                $employer = $employerData['user'];
                                $employerJobs = $employerData['jobs'];
                                $jobCount = $employerData['job_count'];
                                $friendStatus = getFriendStatus($friendService, $userId, $employer->getId());
                            ?>
                                <div class="employer-card">
                                    <div class="employer-header">
                                        <h4><?= htmlspecialchars($employer->getFullName()) ?></h4>
                                        <span class="badge badge-success">Employer</span>
                                    </div>
                                    <p class="employer-meta">
                                        <span class="badge badge-primary"><?= $jobCount ?> <?= $jobCount === 1 ? 'Job' : 'Jobs' ?></span>
                                        <span class="badge badge-info">📍 <?= htmlspecialchars($employer->getLocation()) ?></span>
                                    </p>
                                    <?php if ($employer->getBio()): ?>
                                        <p class="bio-preview"><?= htmlspecialchars(substr($employer->getBio(), 0, 120)) ?>...</p>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($employerJobs)): ?>
                                        <div class="employer-jobs-preview">
                                            <strong>Recent Jobs:</strong>
                                            <ul class="jobs-list-mini">
                                                <?php foreach (array_slice($employerJobs, 0, 3) as $job): ?>
                                                    <li>
                                                        <span class="job-title-mini"><?= htmlspecialchars($job->getTitle()) ?></span>
                                                        <span class="badge badge-small"><?= htmlspecialchars($job->getJobType()) ?></span>
                                                        <span class="badge badge-small badge-success"><?= htmlspecialchars($job->getStatus()) ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                                <?php if (count($employerJobs) > 3): ?>
                                                    <li class="more-jobs">+<?= count($employerJobs) - 3 ?> more job<?= count($employerJobs) - 3 > 1 ? 's' : '' ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-actions">
                                        <a href="profile.php?id=<?= $employer->getId() ?>" class="btn btn-outline btn-small">View Profile & Jobs</a>
                                        <?php if ($friendStatus === 'none'): ?>
                                            <form method="POST" action="friends.php" style="display: inline;">
                                                <input type="hidden" name="action" value="send_request">
                                                <input type="hidden" name="friend_id" value="<?= $employer->getId() ?>">
                                                <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                                            </form>
                                        <?php elseif ($friendStatus === 'pending'): ?>
                                            <span class="badge">Request Sent</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Friends</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($employees)): ?>
                <div class="section">
                    <h3>Browse Freelancers (<?= count($employees) ?>)</h3>
                    <p class="section-description">Connect with other freelancers in the community</p>
                    <div class="employees-grid">
                        <?php foreach ($employees as $employee): 
                            $friendStatus = getFriendStatus($friendService, $userId, $employee->getId());
                        ?>
                            <div class="employee-card">
                                <h4><?= htmlspecialchars($employee->getFullName()) ?></h4>
                                <p class="employee-meta">
                                    <span class="badge badge-primary">Employee</span>
                                    <span class="badge"><?= htmlspecialchars($employee->getExperienceYears()) ?> years exp</span>
                                    <span class="badge badge-info">📍 <?= htmlspecialchars($employee->getLocation()) ?></span>
                                </p>
                                <p><strong>Skills:</strong> <?= htmlspecialchars($employee->getSkills()) ?></p>
                                <?php if ($employee->getBio()): ?>
                                    <p class="bio-preview"><?= htmlspecialchars(substr($employee->getBio(), 0, 100)) ?>...</p>
                                <?php endif; ?>
                                <div class="card-actions">
                                    <a href="profile.php?id=<?= $employee->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                                    <?php if ($friendStatus === 'none'): ?>
                                        <form method="POST" action="friends.php" style="display: inline;">
                                            <input type="hidden" name="action" value="send_request">
                                            <input type="hidden" name="friend_id" value="<?= $employee->getId() ?>">
                                            <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                                        </form>
                                    <?php elseif ($friendStatus === 'pending'): ?>
                                        <span class="badge">Request Sent</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Friends</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($role === 'employee'): ?>
                    <div class="section">
                        <h3>My Applications (<?= count($applications) ?>)</h3>
                        <?php if (empty($applications)): ?>
                            <p class="empty-state">You haven't applied to any jobs yet.</p>
                        <?php else: ?>
                        <div class="applications-list">
                            <?php 
                            $jobService = new \App\Services\JobService();
                            foreach ($applications as $application): 
                                $job = $jobService->getJobById($application->getJobId());
                                if (!$job) continue;
                            ?>
                                <div class="application-card">
                                    <div class="application-header">
                                        <div>
                                            <h4><?= htmlspecialchars($job->getTitle()) ?></h4>
                                            <p class="application-job-title"><?= htmlspecialchars($job->getJobType()) ?> • <?= htmlspecialchars($job->getLocation()) ?></p>
                                        </div>
                                        <span class="badge badge-<?= $application->getStatus() === 'accepted' ? 'success' : ($application->getStatus() === 'rejected' ? 'danger' : 'info') ?>">
                                            <?= ucfirst($application->getStatus()) ?>
                                        </span>
                                    </div>
                                    <div class="application-details">
                                        <p><strong>Budget:</strong> <?= htmlspecialchars($job->getBudget()) ?></p>
                                        <p><strong>Duration:</strong> <?= htmlspecialchars($job->getDuration()) ?></p>
                                        <p class="application-date">Applied: <?= date('M d, Y H:i', strtotime($application->getCreatedAt())) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Apply Job Modal -->
    <div id="applyModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Apply for Job</h3>
                <button type="button" class="modal-close" onclick="closeApplyModal()">&times;</button>
            </div>
            <form method="POST" action="dashboard.php" class="modal-form">
                <input type="hidden" name="action" value="apply_job">
                <input type="hidden" name="job_id" id="applyJobId">
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" id="applyJobTitle" readonly class="form-control">
                </div>
                <div class="form-group">
                    <label for="cover_letter">Cover Letter (Optional)</label>
                    <textarea id="cover_letter" name="cover_letter" rows="5" placeholder="Tell the employer why you're a good fit for this position..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeApplyModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pay Employee</h3>
                <button type="button" class="modal-close" onclick="closePaymentModal()">&times;</button>
            </div>
            <form method="POST" action="payments.php" class="modal-form">
                <input type="hidden" name="action" value="create_payment">
                <input type="hidden" name="job_id" id="paymentJobId">
                <input type="hidden" name="employee_id" id="paymentEmployeeId">
                
                <div class="form-group">
                    <label>Employee</label>
                    <input type="text" id="paymentEmployeeName" readonly class="form-control">
                </div>
                <div class="form-group">
                    <label>Job</label>
                    <input type="text" id="paymentJobTitle" readonly class="form-control">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="payment_amount">Amount ($) *</label>
                        <input type="number" id="payment_amount" name="amount" step="0.01" min="0" required>
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
                    <label for="payment_description">Description</label>
                    <textarea id="payment_description" name="description" rows="3" placeholder="Payment description..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closePaymentModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Job Modal -->
    <?php if ($role === 'employer'): ?>
    <div id="editJobModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit Job</h3>
                <button type="button" class="modal-close" onclick="closeEditJobModal()">&times;</button>
            </div>
            <form method="POST" action="dashboard.php" class="modal-form" id="editJobForm">
                <input type="hidden" name="action" value="update_job">
                <input type="hidden" name="job_id" id="editJobId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_title">Job Title *</label>
                        <input type="text" id="edit_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_job_type">Job Type *</label>
                        <select id="edit_job_type" name="job_type" required>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_description">Description *</label>
                    <textarea id="edit_description" name="description" rows="4" required></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_skills_required">Required Skills *</label>
                        <input type="text" id="edit_skills_required" name="skills_required" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_budget">Budget *</label>
                        <input type="text" id="edit_budget" name="budget" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="edit_duration">Duration *</label>
                        <input type="text" id="edit_duration" name="duration" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location *</label>
                        <input type="text" id="edit_location" name="location" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit_deadline">Deadline (Optional)</label>
                    <input type="date" id="edit_deadline" name="deadline">
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn-outline" onclick="closeEditJobModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Job Search and Filter
        const jobSearch = document.getElementById('jobSearch');
        const jobTypeFilter = document.getElementById('jobTypeFilter');
        const locationFilter = document.getElementById('locationFilter');
        const jobsGrid = document.getElementById('jobsGrid');
        
        if (jobSearch && jobTypeFilter && locationFilter) {
            const allJobCards = Array.from(jobsGrid.querySelectorAll('.job-card'));
            
            function filterJobs() {
                const searchTerm = jobSearch.value.toLowerCase();
                const typeFilter = jobTypeFilter.value;
                const locationFilterValue = locationFilter.value.toLowerCase();
                
                allJobCards.forEach(card => {
                    const title = card.querySelector('h4')?.textContent.toLowerCase() || '';
                    const description = card.textContent.toLowerCase();
                    const jobType = card.querySelector('.badge')?.textContent || '';
                    const location = Array.from(card.querySelectorAll('p')).find(p => p.textContent.includes('Location:'))?.textContent.toLowerCase() || '';
                    
                    const matchesSearch = !searchTerm || title.includes(searchTerm) || description.includes(searchTerm);
                    const matchesType = !typeFilter || jobType.includes(typeFilter);
                    const matchesLocation = !locationFilterValue || location.includes(locationFilterValue);
                    
                    card.style.display = (matchesSearch && matchesType && matchesLocation) ? 'block' : 'none';
                });
            }
            
            jobSearch.addEventListener('input', filterJobs);
            jobTypeFilter.addEventListener('change', filterJobs);
            locationFilter.addEventListener('change', filterJobs);
        }

        // Apply Modal
        function openApplyModal(jobId, jobTitle) {
            document.getElementById('applyJobId').value = jobId;
            document.getElementById('applyJobTitle').value = jobTitle;
            document.getElementById('applyModal').style.display = 'flex';
        }

        function closeApplyModal() {
            document.getElementById('applyModal').style.display = 'none';
            document.getElementById('cover_letter').value = '';
        }

        // Payment Modal
        function openPaymentModal(jobId, employeeId, employeeName, jobTitle) {
            document.getElementById('paymentJobId').value = jobId;
            document.getElementById('paymentEmployeeId').value = employeeId;
            document.getElementById('paymentEmployeeName').value = employeeName;
            document.getElementById('paymentJobTitle').value = jobTitle;
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            document.getElementById('payment_amount').value = '';
            document.getElementById('payment_description').value = '';
        }

        // Edit Job Modal
        <?php if ($role === 'employer'): ?>
        const jobData = <?= json_encode(array_map(fn($job) => $job->toArray(), $jobs)) ?>;
        
        function openEditJobModal(jobId) {
            const job = jobData.find(j => j.id === jobId);
            if (!job) return;
            
            document.getElementById('editJobId').value = job.id;
            document.getElementById('edit_title').value = job.title;
            document.getElementById('edit_job_type').value = job.job_type;
            document.getElementById('edit_description').value = job.description;
            document.getElementById('edit_skills_required').value = job.skills_required;
            document.getElementById('edit_budget').value = job.budget;
            document.getElementById('edit_duration').value = job.duration;
            document.getElementById('edit_location').value = job.location;
            document.getElementById('edit_deadline').value = job.deadline || '';
            
            document.getElementById('editJobModal').style.display = 'flex';
        }

        function closeEditJobModal() {
            document.getElementById('editJobModal').style.display = 'none';
        }
        <?php endif; ?>

        // Close modals when clicking outside
        window.onclick = function(event) {
            const applyModal = document.getElementById('applyModal');
            if (event.target === applyModal) {
                closeApplyModal();
            }
            const paymentModal = document.getElementById('paymentModal');
            if (event.target === paymentModal) {
                closePaymentModal();
            }
            <?php if ($role === 'employer'): ?>
            const editJobModal = document.getElementById('editJobModal');
            if (event.target === editJobModal) {
                closeEditJobModal();
            }
            <?php endif; ?>
        }
    </script>
</body>
</html>
