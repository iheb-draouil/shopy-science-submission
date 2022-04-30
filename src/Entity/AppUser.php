<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\AppUserRepository;

#[ORM\Entity(repositoryClass: AppUserRepository::class)]
class AppUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 6, max: 20)]
    public $username;

    #[ORM\Column(type: 'string')]
    #[Assert\Type('string')]
    #[Assert\Length(min: 5, max: 15)]
    public $password;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 20)]
    public $first_name;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 20)]
    public $last_name;
}
