<?php

namespace App\Services;

use App\Models\User;
use App\Exceptions\AuthenticationException;
use App\Exceptions\ValidationException;

class AuthService
{
    private JsonDatabase $db;
    private ValidationService $validator;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/database.php';
        $this->db = new JsonDatabase($config['users_file']);
        $this->validator = new ValidationService();
    }

    public function register(array $data): User
    {
        $data = $this->validator->validateRegistration($data);

        if ($this->emailExists($data['email'])) {
            throw new ValidationException(['email' => 'Email already registered']);
        }

        $userData = [
            'email' => strtolower(trim($data['email'])),
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'first_name' => trim($data['first_name']),
            'last_name' => trim($data['last_name']),
            'role' => $data['role'],
            'phone' => trim($data['phone']),
            'location' => trim($data['location']),
            'skills' => trim($data['skills'] ?? ''),
            'bio' => trim($data['bio'] ?? ''),
            'experience_years' => (int)($data['experience_years'] ?? 0),
            'created_at' => date('Y-m-d H:i:s')
        ];

        $userId = $this->db->create($userData);
        $userData['id'] = $userId;

        return new User($userData);
    }

    public function login(array $data): User
    {
        $data = $this->validator->validateLogin($data);
        $email = strtolower(trim($data['email']));

        $users = $this->db->findWhere(fn($user) => strtolower($user['email']) === $email);

        if (empty($users) || !password_verify($data['password'], $users[0]['password'])) {
            throw new AuthenticationException('Invalid email or password');
        }

        return new User($users[0]);
    }

    public function getUserById(int $id): ?User
    {
        $userData = $this->db->findById($id);
        return $userData ? new User($userData) : null;
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->db->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->db->delete($id);
    }

    public function getAllUsers(): array
    {
        return array_map(fn($data) => new User($data), $this->db->readAll());
    }

    public function getUsersByRole(string $role): array
    {
        return array_map(fn($data) => new User($data), 
            $this->db->findWhere(fn($user) => $user['role'] === $role)
        );
    }

    private function emailExists(string $email): bool
    {
        $email = strtolower($email);
        $users = $this->db->findWhere(fn($user) => strtolower($user['email']) === $email);
        return !empty($users);
    }
}
