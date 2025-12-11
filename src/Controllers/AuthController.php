<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Middleware\SessionMiddleware;
use App\Exceptions\ValidationException;
use App\Exceptions\AuthenticationException;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(): array
    {
        try {
            $data = $_POST;
            $user = $this->authService->register($data);

            SessionMiddleware::set('user_id', $user->getId());
            SessionMiddleware::set('user_role', $user->getRole());
			SessionMiddleware::setFlash('success', 'Registration successful! Welcome aboard.');

            return [
                'success' => true,
				'redirect' => 'dashboard.php'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->getErrors()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function login(): array
    {
        try {
            $data = $_POST;
            $user = $this->authService->login($data);

            SessionMiddleware::set('user_id', $user->getId());
            SessionMiddleware::set('user_role', $user->getRole());
            SessionMiddleware::setFlash('success', 'Welcome back, ' . $user->getFirstName() . '!');

            return [
                'success' => true,
				'redirect' => 'dashboard.php'
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'errors' => $e->getErrors()
            ];
        } catch (AuthenticationException $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'errors' => ['general' => $e->getMessage()]
            ];
        }
    }

    public function logout(): void
    {
        SessionMiddleware::start();
        SessionMiddleware::destroy();
        header('Location: login.php');
        exit;
    }
}
