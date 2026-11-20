<?php

namespace App\Entity;

use App\Repository\BibliotecaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BibliotecaRepository::class)]
class Biblioteca
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $nombre;

    #[ORM\Column]
    private \DateTimeImmutable $creadoEn;

    public const ANCHO_TARJETA_PT_DEFAULT = 216;
    public const ALTO_TARJETA_PT_DEFAULT = 360;

    #[ORM\Column(nullable: true)]
    private ?int $anchoTarjetaPt = null;

    #[ORM\Column(nullable: true)]
    private ?int $altoTarjetaPt = null;

    public const PREFIJO_ADQUISICION_DEFAULT = 'GM(1)';

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $prefijoAdquisicionPredeterminado = null;

    #[ORM\Column(nullable: true)]
    private ?float $autorMarginTopPt = null;

    #[ORM\Column(nullable: true)]
    private ?float $tituloMarginTopPt = null;

    #[ORM\Column(nullable: true)]
    private ?float $prefijoDesplazamientoXPt = null;

    public function __construct()
    {
        $this->creadoEn = new \DateTimeImmutable();
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

    public function getCreadoEn(): \DateTimeImmutable
    {
        return $this->creadoEn;
    }

    public function getAnchoTarjetaPt(): int
    {
        return $this->anchoTarjetaPt ?? self::ANCHO_TARJETA_PT_DEFAULT;
    }

    public function setAnchoTarjetaPt(?int $anchoTarjetaPt): static
    {
        $this->anchoTarjetaPt = $anchoTarjetaPt;

        return $this;
    }

    public function getAltoTarjetaPt(): int
    {
        return $this->altoTarjetaPt ?? self::ALTO_TARJETA_PT_DEFAULT;
    }

    public function setAltoTarjetaPt(?int $altoTarjetaPt): static
    {
        $this->altoTarjetaPt = $altoTarjetaPt;

        return $this;
    }

    public function getPrefijoAdquisicionPredeterminado(): string
    {
        return $this->prefijoAdquisicionPredeterminado ?? self::PREFIJO_ADQUISICION_DEFAULT;
    }

    public function setPrefijoAdquisicionPredeterminado(?string $prefijoAdquisicionPredeterminado): static
    {
        $this->prefijoAdquisicionPredeterminado = $prefijoAdquisicionPredeterminado;

        return $this;
    }

    public function getAutorMarginTopPt(): ?float
    {
        return $this->autorMarginTopPt;
    }

    public function setAutorMarginTopPt(?float $autorMarginTopPt): static
    {
        $this->autorMarginTopPt = $autorMarginTopPt;

        return $this;
    }

    public function getTituloMarginTopPt(): ?float
    {
        return $this->tituloMarginTopPt;
    }

    public function setTituloMarginTopPt(?float $tituloMarginTopPt): static
    {
        $this->tituloMarginTopPt = $tituloMarginTopPt;

        return $this;
    }

    public function getPrefijoDesplazamientoXPt(): ?float
    {
        return $this->prefijoDesplazamientoXPt;
    }

    public function setPrefijoDesplazamientoXPt(?float $prefijoDesplazamientoXPt): static
    {
        $this->prefijoDesplazamientoXPt = $prefijoDesplazamientoXPt;

        return $this;
    }
}
