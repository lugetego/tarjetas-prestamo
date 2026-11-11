<?php

namespace App\Entity;

use App\Repository\InicioSesionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InicioSesionRepository::class)]
class InicioSesion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column]
    private \DateTimeImmutable $fecha;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->fecha = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getFecha(): \DateTimeImmutable
    {
        return $this->fecha;
    }
}
