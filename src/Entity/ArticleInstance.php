<?php

namespace App\Entity;

use App\Repository\ArticleInstanceRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleInstanceRepository::class)]
class ArticleInstance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(36)]
    public $code;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'articleInstances')]
    #[ORM\JoinColumn(nullable: false)]
    public $article;
}
