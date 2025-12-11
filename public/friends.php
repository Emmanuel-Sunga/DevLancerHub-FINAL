<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\FriendController;

SessionMiddleware::start();
AuthMiddleware::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $controller = new FriendController();
    
    switch ($_POST['action']) {
        case 'send_request':
            $result = $controller->sendRequest();
            break;
        case 'accept_request':
            $result = $controller->acceptRequest();
            break;
        case 'reject_request':
            $result = $controller->rejectRequest();
            break;
        case 'remove_friend':
            $result = $controller->removeFriend();
            break;
    }
    
    if (isset($result) && $result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}

$controller = new FriendController();
$data = $controller->index();
$friends = $data['friends'];
$pendingRequests = $data['pending_requests'];
$sentRequests = $data['sent_requests'];

$flashMessage = SessionMiddleware::getFlash('success');
$userId = SessionMiddleware::getUserId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">
                    <h1>DevLancerHub</h1>
                    <p class="tagline">Friends</p>
                </a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="messages.php" class="nav-link">Messages</a>
                <a href="friends.php" class="nav-link active">Friends</a>
                <a href="payments.php" class="nav-link">Payments</a>
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

            <div class="dashboard-header">
                <h2>My Friends</h2>
            </div>

            <div class="section">
                <h3>Pending Friend Requests (<?= count($pendingRequests) ?>)</h3>
                <?php if (empty($pendingRequests)): ?>
                    <p class="empty-state">No pending friend requests.</p>
                <?php else: ?>
                    <div class="friends-grid">
                        <?php foreach ($pendingRequests as $item): ?>
                            <div class="friend-card">
                                <h4><?= htmlspecialchars($item['user']->getFullName()) ?></h4>
                                <p class="employee-meta">
                                    <span class="badge <?= $item['user']->getRole() === 'employee' ? 'badge-primary' : 'badge-success' ?>">
                                        <?= $item['user']->getRole() === 'employee' ? 'Employee' : 'Employer' ?>
                                    </span>
                                    <span class="badge badge-info">📍 <?= htmlspecialchars($item['user']->getLocation()) ?></span>
                                </p>
                                <div class="card-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="accept_request">
                                        <input type="hidden" name="request_id" value="<?= $item['request']->getId() ?>">
                                        <button type="submit" class="btn btn-primary btn-small">Accept</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject_request">
                                        <input type="hidden" name="request_id" value="<?= $item['request']->getId() ?>">
                                        <button type="submit" class="btn btn-outline btn-small">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section">
                <h3>My Friends (<?= count($friends) ?>)</h3>
                <?php if (empty($friends)): ?>
                    <p class="empty-state">You don't have any friends yet. Browse users and send friend requests!</p>
                <?php else: ?>
                    <div class="friends-grid">
                        <?php foreach ($friends as $item): ?>
                            <div class="friend-card">
                                <h4><?= htmlspecialchars($item['user']->getFullName()) ?></h4>
                                <p class="employee-meta">
                                    <span class="badge <?= $item['user']->getRole() === 'employee' ? 'badge-primary' : 'badge-success' ?>">
                                        <?= $item['user']->getRole() === 'employee' ? 'Employee' : 'Employer' ?>
                                    </span>
                                    <span class="badge badge-info">📍 <?= htmlspecialchars($item['user']->getLocation()) ?></span>
                                </p>
                                <div class="card-actions">
                                    <a href="messages.php?user_id=<?= $item['user']->getId() ?>" class="btn btn-primary btn-small">Message</a>
                                    <a href="profile.php?id=<?= $item['user']->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="remove_friend">
                                        <input type="hidden" name="request_id" value="<?= $item['friend']->getId() ?>">
                                        <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Remove this friend?')">Remove</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="section">
                <h3>Sent Requests (<?= count($sentRequests) ?>)</h3>
                <?php if (empty($sentRequests)): ?>
                    <p class="empty-state">No sent requests.</p>
                <?php else: ?>
                    <div class="friends-grid">
                        <?php foreach ($sentRequests as $item): ?>
                            <div class="friend-card">
                                <h4><?= htmlspecialchars($item['user']->getFullName()) ?></h4>
                                <p class="employee-meta">
                                    <span class="badge <?= $item['user']->getRole() === 'employee' ? 'badge-primary' : 'badge-success' ?>">
                                        <?= $item['user']->getRole() === 'employee' ? 'Employee' : 'Employer' ?>
                                    </span>
                                    <span class="badge badge-info">📍 <?= htmlspecialchars($item['user']->getLocation()) ?></span>
                                    <span class="badge">Pending</span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

