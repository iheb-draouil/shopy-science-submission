<?php

namespace App\Security\Hasher;

use Symfony\Component\PasswordHasher\Exception\InvalidPasswordException;
use Symfony\Component\PasswordHasher\Hasher\CheckPasswordLengthTrait;

class CustomHasher
{
    use CheckPasswordLengthTrait;

    private $memory_cost;
    private $time_cost;
    private $threads;

    public function __construct(
        int $memory_cost,
        int $time_cost,
        int $threads,
    ) {
        $this->memory_cost = $memory_cost;
        $this->time_cost = $time_cost;
        $this->threads = $threads;
    }

    public function hash(string $plain_password, string $salt): string
    {
        $full_password = $plain_password . $salt;

        if ($this->isPasswordTooLong($full_password)) {
            throw new InvalidPasswordException();
        }

        $hashed_password = password_hash($full_password, PASSWORD_ARGON2ID, [
            'memory_cost' => $this->memory_cost,
            'time_cost' => $this->time_cost,
            'threads' => $this->threads,
        ]);

        return $hashed_password;
    }

    public function verify(string $plain_password, string $salt, string $hashed_password): bool
    {
        $full_password = $plain_password . $salt;

        if ($this->isPasswordTooLong($full_password)) {
            throw new InvalidPasswordException();
        }

        return password_verify($full_password, $hashed_password);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}