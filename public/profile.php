<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\ProfileController;
use App\Services\FriendService;

SessionMiddleware::start();
AuthMiddleware::requireAuth();

$userId = isset($_GET['id']) ? (int)$_GET['id'] : SessionMiddleware::getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $controller = new ProfileController();
    $result = $controller->update();
    
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}

$controller = new ProfileController();
$data = $controller->show($userId);
$user = $data['user'];
$isOwnProfile = $data['isOwnProfile'];
$jobs = $data['jobs'] ?? [];

$currentUserId = SessionMiddleware::getUserId();
$friendService = new FriendService();
$isFriend = !$isOwnProfile && $friendService->isFriend($currentUserId, $userId);
$hasPendingRequest = false;
if (!$isOwnProfile && !$isFriend) {
    $sentRequests = $friendService->getSentRequests($currentUserId);
    foreach ($sentRequests as $request) {
        if ($request->getFriendId() === $userId) {
            $hasPendingRequest = true;
            break;
        }
    }
}

$flashMessage = SessionMiddleware::getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user->getFullName()) ?> - Profile</title>
	<link rel="stylesheet" href="css/style.css">
	<script src="js/profile.js" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
				<a href="dashboard.php">
					<h1>DevLancerHub</h1>
					<p class="tagline">Profile</p>
                </a>
            </div>
            <div class="nav-links">
				<a href="dashboard.php" class="nav-link">Dashboard</a>
				<a href="messages.php" class="nav-link">Messages</a>
				<a href="friends.php" class="nav-link">Friends</a>
				<a href="payments.php" class="nav-link">Payments</a>
				<a href="profile.php?id=<?= SessionMiddleware::getUserId() ?>" class="nav-link active">My Profile</a>
				<a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="container">
            <?php if ($flashMessage): ?>
                <div class="alert alert-success"><?= htmlspecialchars($flashMessage) ?></div>
            <?php endif; ?>

            <div class="profile-header">
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user->getFullName()) ?></h2>
                    <p class="profile-role"><?= $user->getRole() === 'employee' ? 'Freelancer' : 'Client' ?></p>
                    <p class="profile-location">📍 <?= htmlspecialchars($user->getLocation()) ?></p>
                    <?php if ($isOwnProfile): ?>
                        <button id="editProfileBtn" class="btn btn-primary btn-small">Edit Profile</button>
                    <?php elseif ($isFriend): ?>
                        <a href="messages.php?user_id=<?= $user->getId() ?>" class="btn btn-primary btn-small">Message</a>
                        <span class="badge badge-success">Friends</span>
                    <?php elseif ($hasPendingRequest): ?>
                        <span class="badge">Friend Request Sent</span>
                    <?php else: ?>
                        <form method="POST" action="friends.php" style="display: inline;">
                            <input type="hidden" name="action" value="send_request">
                            <input type="hidden" name="friend_id" value="<?= $user->getId() ?>">
                            <button type="submit" class="btn btn-primary btn-small">Add Friend</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-content">
                <div class="profile-section">
                    <h3>Contact Information</h3>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user->getEmail()) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($user->getPhone()) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($user->getLocation()) ?></p>
                </div>

                <?php if ($user->getRole() === 'employee'): ?>
                    <div class="profile-section">
                        <h3>Professional Details</h3>
                        <p><strong>Experience:</strong> <?= htmlspecialchars($user->getExperienceYears()) ?> years</p>
                        <p><strong>Skills:</strong></p>
                        <div class="skills-container">
                            <?php
                            $skills = array_map('trim', explode(',', $user->getSkills()));
                            foreach ($skills as $skill): ?>
                                <span class="skill-badge"><?= htmlspecialchars($skill) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($user->getBio()): ?>
                    <div class="profile-section">
                        <h3>About</h3>
                        <p><?= nl2br(htmlspecialchars($user->getBio())) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($user->getRole() === 'employer' && !empty($jobs)): ?>
                    <div class="profile-section">
                        <h3>Posted Jobs (<?= count($jobs) ?>)</h3>
                        <div class="jobs-list">
                            <?php foreach ($jobs as $job): ?>
                                <div class="job-item">
                                    <h4><?= htmlspecialchars($job->getTitle()) ?></h4>
                                    <p class="job-meta">
                                        <span class="badge"><?= htmlspecialchars($job->getJobType()) ?></span>
                                        <span class="badge badge-success"><?= htmlspecialchars($job->getStatus()) ?></span>
                                    </p>
                                    <p><?= htmlspecialchars(substr($job->getDescription(), 0, 200)) ?>...</p>
                                    <p><strong>Budget:</strong> <?= htmlspecialchars($job->getBudget()) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="profile-section">
                    <p class="text-muted">Member since <?= date('M Y', strtotime($user->getCreatedAt())) ?></p>
                </div>
            </div>

            <?php if ($isOwnProfile): ?>
                <div id="editProfileModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Edit Profile</h3>
                            <button class="modal-close" id="closeModal">&times;</button>
                        </div>
						<form method="POST" action="profile.php?id=<?= $user->getId() ?>" class="modal-form">
                            <input type="hidden" name="action" value="update_profile">

                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user->getFirstName()) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user->getLastName()) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user->getPhone()) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" value="<?= htmlspecialchars($user->getLocation()) ?>" required>
                            </div>

                            <?php if ($user->getRole() === 'employee'): ?>
                                <div class="form-group">
                                    <label for="skills">Skills</label>
                                    <textarea id="skills" name="skills" rows="3" required><?= htmlspecialchars($user->getSkills()) ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="experience_years">Years of Experience</label>
                                    <input type="number" id="experience_years" name="experience_years" min="0" value="<?= htmlspecialchars($user->getExperienceYears()) ?>" required>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="bio">Bio</label>
                                <textarea id="bio" name="bio" rows="5"><?= htmlspecialchars($user->getBio()) ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
