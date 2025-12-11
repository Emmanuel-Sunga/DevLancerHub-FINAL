<?php

namespace App\Middleware;

class AuthMiddleware
{
    public static function requireAuth(): void
    {
        SessionMiddleware::start();
        if (!SessionMiddleware::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (SessionMiddleware::getUserRole() !== $role) {
            header('Location: dashboard.php');
            exit;
        }
    }

    public static function redirectIfAuthenticated(): void
    {
        SessionMiddleware::start();
        if (SessionMiddleware::isLoggedIn()) {
            header('Location: dashboard.php');
            exit;
        }
    }
}
