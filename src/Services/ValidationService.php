<?php

namespace App\Services;

use App\Exceptions\ValidationException;

class ValidationService
{
    public function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } else {
            $password = $data['password'];
            if (!preg_match('/[A-Z]/', $password)) {
                $errors['password'] = 'Password must contain at least one uppercase letter';
            } elseif (!preg_match('/[a-z]/', $password)) {
                $errors['password'] = 'Password must contain at least one lowercase letter';
            } elseif (!preg_match('/[0-9]/', $password)) {
                $errors['password'] = 'Password must contain at least one number';
            }
        }

        if (empty($data['confirm_password'])) {
            $errors['confirm_password'] = 'Please confirm your password';
        } elseif ($data['password'] !== $data['confirm_password']) {
            $errors['confirm_password'] = 'Passwords do not match';
        }

        if (empty($data['first_name'])) {
            $errors['first_name'] = 'First name is required';
        } elseif (strlen($data['first_name']) < 2) {
            $errors['first_name'] = 'First name must be at least 2 characters';
        }

        if (empty($data['last_name'])) {
            $errors['last_name'] = 'Last name is required';
        } elseif (strlen($data['last_name']) < 2) {
            $errors['last_name'] = 'Last name must be at least 2 characters';
        }

        if (empty($data['role']) || !in_array($data['role'], ['employee', 'employer'])) {
            $errors['role'] = 'Please select a valid role';
        }

        if (empty($data['phone'])) {
            $errors['phone'] = 'Phone number is required';
        } elseif (!preg_match('/^[0-9\-\+\(\)\s]{10,}$/', $data['phone'])) {
            $errors['phone'] = 'Please enter a valid phone number';
        }

        if (empty($data['location'])) {
            $errors['location'] = 'Location is required';
        }

        if ($data['role'] === 'employee') {
            if (empty($data['skills'])) {
                $errors['skills'] = 'Skills are required for employees';
            }
            if (!isset($data['experience_years']) || $data['experience_years'] < 0) {
                $errors['experience_years'] = 'Please enter valid years of experience';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    public function validateLogin(array $data): array
    {
        $errors = [];

        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }

        if (empty($data['password'])) {
            $errors['password'] = 'Password is required';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    public function validateJob(array $data): array
    {
        $errors = [];

        $requiredFields = [
            'title' => ['min' => 3, 'message' => 'Job title must be at least 3 characters'],
            'description' => ['min' => 10, 'message' => 'Job description must be at least 10 characters'],
            'skills_required' => ['min' => 0, 'message' => 'Required skills must be specified'],
            'budget' => ['min' => 0, 'message' => 'Budget is required'],
            'duration' => ['min' => 0, 'message' => 'Duration is required'],
            'location' => ['min' => 0, 'message' => 'Location is required']
        ];

        foreach ($requiredFields as $field => $rules) {
            $value = trim($data[$field] ?? '');
            if (empty($value)) {
                $errors[$field] = $rules['message'];
            } elseif ($rules['min'] > 0 && strlen($value) < $rules['min']) {
                $errors[$field] = $rules['message'];
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
