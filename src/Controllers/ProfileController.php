<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\JobService;
use App\Middleware\SessionMiddleware;

class ProfileController
{
    private AuthService $authService;
    private JobService $jobService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->jobService = new JobService();
    }

    public function show(int $userId): array
    {
        $user = $this->authService->getUserById($userId);

        if (!$user) {
            header('Location: dashboard.php');
            exit;
        }

        $data = [
            'user' => $user,
            'isOwnProfile' => SessionMiddleware::getUserId() === $userId
        ];

        if ($user->getRole() === 'employer') {
            $data['jobs'] = $this->jobService->getJobsByEmployer($userId);
        }

        return $data;
    }

    public function update(): array
    {
        try {
            $userId = SessionMiddleware::getUserId();
            $data = $_POST;
            
            unset($data['email']);
            unset($data['password']);
            unset($data['role']);

            $this->authService->updateUser($userId, $data);

            SessionMiddleware::setFlash('success', 'Profile updated successfully!');

            return [
                'success' => true,
                'redirect' => 'profile.php?id=' . $userId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }
}

