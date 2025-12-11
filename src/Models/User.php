<?php

namespace App\Models;

class User
{
    private ?int $id;
    private string $email;
    private string $password;
    private string $firstName;
    private string $lastName;
    private string $role;
    private string $skills;
    private string $bio;
    private string $phone;
    private string $location;
    private int $experienceYears;
    private string $createdAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->firstName = $data['first_name'] ?? '';
        $this->lastName = $data['last_name'] ?? '';
        $this->role = $data['role'] ?? 'employee';
        $this->skills = $data['skills'] ?? '';
        $this->bio = $data['bio'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->location = $data['location'] ?? '';
        $this->experienceYears = $data['experience_years'] ?? 0;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getSkills(): string
    {
        return $this->skills;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getExperienceYears(): int
    {
        return $this->experienceYears;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'password' => $this->password,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'role' => $this->role,
            'skills' => $this->skills,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'location' => $this->location,
            'experience_years' => $this->experienceYears,
            'created_at' => $this->createdAt
        ];
    }

    public function toSafeArray(): array
    {
        $data = $this->toArray();
        unset($data['password']);
        return $data;
    }
}
