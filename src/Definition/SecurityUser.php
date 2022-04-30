<?php

namespace App\Definition;

use Lcobucci\JWT\UnencryptedToken;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    public int $id;
    public string $username;

    public ?UnencryptedToken $access_token;
    public ?UnencryptedToken $refresh_token;

    public function __construct(
        int $id,
        string $username,
        UnencryptedToken $access_token = null,
        UnencryptedToken $refresh_token = null,
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->access_token = $access_token;
        $this->refresh_token = $refresh_token;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return [];
    }
    
    public function getPassword(): string
    {
        return '';
    }

    public function eraseCredentials()
    {

    }
}
