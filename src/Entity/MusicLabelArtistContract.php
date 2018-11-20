<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MusicLabelArtistContractRepository")
 */
class MusicLabelArtistContract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MusicLabel", inversedBy="musicLabelArtistContracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicLabel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Artist", inversedBy="musicLabelArtistContracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $artist;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTimeImmutable $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getMusicLabel(): ?MusicLabel
    {
        return $this->musicLabel;
    }

    public function setMusicLabel(?MusicLabel $musicLabel): self
    {
        $this->musicLabel = $musicLabel;

        return $this;
    }

    public function getArtist(): ?Artist
    {
        return $this->artist;
    }

    public function setArtist(?Artist $artist): self
    {
        $this->artist = $artist;

        return $this;
    }

    public function validOn(DateTimeImmutable $date): bool
    {
        return $this->validBetween($date, $date);
    }

    public function validBetween(DateTimeImmutable $startDate, DateTimeImmutable $endDate = null): bool
    {
        $now = new DateTimeImmutable('now');
        $endSelf = $this->endDate ?? $now;
        $endDate = $endDate ?? $now;

        if ($endSelf < $this->startDate || $endDate < $startDate) {
            throw new \OutOfRangeException('End dates must be after start dates.');
        }

        return ($this->startDate <= $endDate) && ($endSelf >= $startDate);
    }
}
