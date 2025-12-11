<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Middleware\SessionMiddleware;
use App\Middleware\AuthMiddleware;
use App\Controllers\MessageController;

SessionMiddleware::start();
AuthMiddleware::requireAuth();

$messageController = new MessageController();
$userId = SessionMiddleware::getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    $result = $messageController->send();
    if ($result['success']) {
        header('Location: ' . $result['redirect']);
        exit;
    }
}

$otherUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

if ($otherUserId) {
    $conversationData = $messageController->conversation($otherUserId);
    $otherUser = $conversationData['other_user'] ?? null;
    $messages = $conversationData['messages'] ?? [];
} else {
    $otherUser = null;
    $messages = [];
}

$conversationsData = $messageController->conversations();
$conversations = $conversationsData['conversations'];
$totalUnread = $conversationsData['total_unread'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .messages-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 1rem;
            height: calc(100vh - 200px);
        }
        .conversations-list {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            overflow-y: auto;
            border: 1px solid var(--border-color);
        }
        .conversation-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background 0.2s;
        }
        .conversation-item:hover {
            background: var(--light-color);
        }
        .conversation-item.active {
            background: var(--primary-color);
            color: white;
        }
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .unread-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.75rem;
        }
        .message-area {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
        }
        .messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .message-item {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            max-width: 70%;
        }
        .message-item.sent {
            background: var(--primary-color);
            color: white;
            margin-left: auto;
        }
        .message-item.received {
            background: var(--light-color);
            color: var(--text-color);
        }
        .message-time {
            font-size: 0.75rem;
            opacity: 0.7;
            margin-top: 0.25rem;
        }
        .message-form {
            display: flex;
            gap: 0.5rem;
        }
        .message-form textarea {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            resize: none;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="dashboard.php">
                    <h1>DevLancerHub</h1>
                    <p class="tagline">Messages <?= $totalUnread > 0 ? "($totalUnread)" : "" ?></p>
                </a>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="messages.php" class="nav-link active">Messages</a>
                <a href="friends.php" class="nav-link">Friends</a>
                <a href="payments.php" class="nav-link">Payments</a>
                <a href="profile.php?id=<?= $userId ?>" class="nav-link">My Profile</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="container">
            <div class="messages-container">
                <div class="conversations-list">
                    <h3>Conversations</h3>
                    <?php if (empty($conversations)): ?>
                        <p class="empty-state">No conversations yet.</p>
                    <?php else: ?>
                        <?php foreach ($conversations as $conv): ?>
                            <a href="messages.php?user_id=<?= $conv['user']->getId() ?>" style="text-decoration: none; color: inherit;">
                                <div class="conversation-item <?= $otherUserId === $conv['user']->getId() ? 'active' : '' ?>">
                                    <div class="conversation-header">
                                        <strong><?= htmlspecialchars($conv['user']->getFullName()) ?></strong>
                                        <?php if ($conv['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?= $conv['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p style="font-size: 0.85rem; margin-top: 0.5rem; opacity: 0.8;">
                                        <?= htmlspecialchars(substr($conv['last_message']->getMessage(), 0, 50)) ?>...
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="message-area">
                    <?php if ($otherUser): ?>
                        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
                            <h3><?= htmlspecialchars($otherUser->getFullName()) ?></h3>
                            <a href="profile.php?id=<?= $otherUser->getId() ?>" class="btn btn-outline btn-small">View Profile</a>
                        </div>

                        <div class="messages-list">
                            <?php if (empty($messages)): ?>
                                <p class="empty-state">No messages yet. Start the conversation!</p>
                            <?php else: ?>
                                <?php foreach ($messages as $message): ?>
                                    <div class="message-item <?= $message->getSenderId() === $userId ? 'sent' : 'received' ?>">
                                        <div><?= nl2br(htmlspecialchars($message->getMessage())) ?></div>
                                        <div class="message-time">
                                            <?= date('M d, Y H:i', strtotime($message->getCreatedAt())) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="message-form">
                            <input type="hidden" name="action" value="send_message">
                            <input type="hidden" name="receiver_id" value="<?= $otherUser->getId() ?>">
                            <textarea name="message" rows="3" placeholder="Type your message..." required></textarea>
                            <button type="submit" class="btn btn-primary">Send</button>
                        </form>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem;">
                            <p>Select a conversation from the list to start messaging</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

