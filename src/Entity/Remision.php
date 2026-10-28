<?php

namespace App\Entity;

use App\Repository\RemisionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RemisionRepository::class)]
class Remision
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $nombre;

    #[ORM\Column(length: 50)]
    private string $prefijoAdquisicion = 'GM(1)';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $archivoOriginal = null;

    #[ORM\Column]
    private \DateTimeImmutable $creadoEn;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Biblioteca $biblioteca;

    /** @var Collection<int, Tarjeta> */
    #[ORM\OneToMany(targetEntity: Tarjeta::class, mappedBy: 'remision', orphanRemoval: true, cascade: ['persist'])]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $tarjetas;

    public function __construct()
    {
        $this->creadoEn = new \DateTimeImmutable();
        $this->tarjetas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getPrefijoAdquisicion(): string
    {
        return $this->prefijoAdquisicion;
    }

    public function setPrefijoAdquisicion(string $prefijoAdquisicion): static
    {
        $this->prefijoAdquisicion = $prefijoAdquisicion;

        return $this;
    }

    public function getArchivoOriginal(): ?string
    {
        return $this->archivoOriginal;
    }

    public function setArchivoOriginal(?string $archivoOriginal): static
    {
        $this->archivoOriginal = $archivoOriginal;

        return $this;
    }

    public function getCreadoEn(): \DateTimeImmutable
    {
        return $this->creadoEn;
    }

    public function getBiblioteca(): Biblioteca
    {
        return $this->biblioteca;
    }

    public function setBiblioteca(Biblioteca $biblioteca): static
    {
        $this->biblioteca = $biblioteca;

        return $this;
    }

    /** @return Collection<int, Tarjeta> */
    public function getTarjetas(): Collection
    {
        return $this->tarjetas;
    }

    public function addTarjeta(Tarjeta $tarjeta): static
    {
        if (!$this->tarjetas->contains($tarjeta)) {
            $this->tarjetas->add($tarjeta);
            $tarjeta->setRemision($this);
        }

        return $this;
    }
}
