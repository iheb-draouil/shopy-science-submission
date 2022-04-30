<?php

namespace App\Entity;

use App\Repository\ActiveSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActiveSessionRepository::class)]
class ActiveSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    public $id;

    #[ORM\Column(type: 'string', length: 600)]
    public $jwt;

    #[ORM\Column(type: 'datetime_immutable')]
    public $started_at;

    #[ORM\Column(type: 'datetime_immutable')]
    public $expires_at;

    #[ORM\ManyToOne(targetEntity: AppUser::class, inversedBy: 'active_sessions')]
    #[ORM\JoinColumn(nullable: false)]
    public $app_user;
}
