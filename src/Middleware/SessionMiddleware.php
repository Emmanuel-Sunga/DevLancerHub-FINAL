<?php

namespace App\Middleware;

class SessionMiddleware
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserRole(): ?string
    {
        return self::get('user_role');
    }

    public static function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): ?string
    {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }
}
