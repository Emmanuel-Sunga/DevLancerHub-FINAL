<?php

namespace App\Controllers;

use App\Services\FriendService;
use App\Services\AuthService;
use App\Middleware\SessionMiddleware;

class FriendController
{
    private FriendService $friendService;
    private AuthService $authService;

    public function __construct()
    {
        $this->friendService = new FriendService();
        $this->authService = new AuthService();
    }

    public function sendRequest(): array
    {
        try {
            $userId = SessionMiddleware::getUserId();
            $friendId = (int)$_POST['friend_id'];

            if ($userId === $friendId) {
                return [
                    'success' => false,
                    'errors' => ['general' => 'Cannot send friend request to yourself']
                ];
            }

            $this->friendService->sendFriendRequest($userId, $friendId);
            SessionMiddleware::setFlash('success', 'Friend request sent successfully!');

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

    public function acceptRequest(): array
    {
        try {
            $requestId = (int)$_POST['request_id'];
            $this->friendService->acceptFriendRequest($requestId);
            SessionMiddleware::setFlash('success', 'Friend request accepted!');

            return [
                'success' => true,
                'redirect' => 'friends.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function rejectRequest(): array
    {
        try {
            $requestId = (int)$_POST['request_id'];
            $this->friendService->rejectFriendRequest($requestId);
            SessionMiddleware::setFlash('success', 'Friend request rejected.');

            return [
                'success' => true,
                'redirect' => 'friends.php'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function removeFriend(): array
    {
        try {
            $requestId = (int)$_POST['request_id'];
            $this->friendService->removeFriend($requestId);
            SessionMiddleware::setFlash('success', 'Friend removed.');

            return [
                'success' => true,
                'redirect' => 'friends.php'
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
        $friends = $this->friendService->getFriends($userId);
        $pendingRequests = $this->friendService->getPendingRequests($userId);
        $sentRequests = $this->friendService->getSentRequests($userId);

        // Batch load user IDs to reduce queries
        $userIds = array_unique(array_merge(
            array_map(fn($f) => $f->getUserId() === $userId ? $f->getFriendId() : $f->getUserId(), $friends),
            array_map(fn($r) => $r->getUserId(), $pendingRequests),
            array_map(fn($r) => $r->getFriendId(), $sentRequests)
        ));
        $users = [];
        foreach ($userIds as $id) {
            $users[$id] = $this->authService->getUserById($id);
        }

        // Get user details for friends
        $friendsWithDetails = array_filter(array_map(function($friend) use ($userId, $users) {
            $friendId = $friend->getUserId() === $userId ? $friend->getFriendId() : $friend->getUserId();
            return ($user = $users[$friendId] ?? null) ? ['friend' => $friend, 'user' => $user] : null;
        }, $friends));

        // Get user details for pending and sent requests
        $pendingWithDetails = array_filter(array_map(fn($r) => ($u = $users[$r->getUserId()] ?? null) ? ['request' => $r, 'user' => $u] : null, $pendingRequests));
        $sentWithDetails = array_filter(array_map(fn($r) => ($u = $users[$r->getFriendId()] ?? null) ? ['request' => $r, 'user' => $u] : null, $sentRequests));

        return [
            'friends' => $friendsWithDetails,
            'pending_requests' => $pendingWithDetails,
            'sent_requests' => $sentWithDetails
        ];
    }
}

