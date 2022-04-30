<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 5, max: 30)]
    public $name;

    #[ORM\Column(type: 'string', length: 300)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 20, max: 300)]
    public $description;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(36)]
    public $code;
}
