<?php

namespace App\Entity;

use App\Repository\TarjetaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarjetaRepository::class)]
class Tarjeta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tarjetas')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Remision $remision;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $adquision = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $titulo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $autor = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $sistema = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $clasificacion = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRemision(): Remision
    {
        return $this->remision;
    }

    public function setRemision(Remision $remision): static
    {
        $this->remision = $remision;

        return $this;
    }

    public function getAdquision(): ?string
    {
        return $this->adquision;
    }

    public function setAdquision(?string $adquision): static
    {
        $this->adquision = $adquision;

        return $this;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(?string $titulo): static
    {
        $this->titulo = $titulo;

        return $this;
    }

    public function getAutor(): ?string
    {
        return $this->autor;
    }

    public function setAutor(?string $autor): static
    {
        $this->autor = $autor;

        return $this;
    }

    public function getSistema(): ?string
    {
        return $this->sistema;
    }

    public function setSistema(?string $sistema): static
    {
        $this->sistema = $sistema;

        return $this;
    }

    public function getClasificacion(): ?string
    {
        return $this->clasificacion;
    }

    public function setClasificacion(?string $clasificacion): static
    {
        $this->clasificacion = $clasificacion;

        return $this;
    }
}
