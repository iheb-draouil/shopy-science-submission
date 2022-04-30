<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

use App\Repository\AppOrderRepository;

#[ORM\Entity(repositoryClass: AppOrderRepository::class)]
class AppOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public $order_number;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 50)]
    public $delivery_name;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 5, max: 50)]
    public $delivery_address;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2, max: 50)]
    public $delivery_country;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Regex('/^\d+$/')]
    #[Assert\Length(min: 3, max: 50)]
    public $delivery_zipcode;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 3, max: 50)]
    public $delivery_city;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Type(Article::class)]
    public Article $article;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    public $quantity;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\PositiveOrZero]
    public $line_price_excl_vat;

    #[ORM\Column(type: 'float')]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    #[Assert\PositiveOrZero]
    public $line_price_incl_vat;

    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(36)]
    public $code;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public $treated;
}
