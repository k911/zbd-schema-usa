<?php

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MusicLabelStreamingServiceContractRepository")
 */
class MusicLabelStreamingServiceContract
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="date_immutable")
     */
    private $startDate;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MusicLabel", inversedBy="musicLabelStreamingServiceContracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicLabel;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\StreamingService", inversedBy="musicLabelStreamingServiceContracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $streamingService;

    /**
     * @ORM\Column(type="integer")
     */
    private $costPerStream;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getEndDateDT(): \DateTime
    {
        if (null === $this->endDate) {
            return new \DateTime('now');
        }

        return (new \DateTime())->setTimestamp($this->endDate->getTimestamp());
    }

    public function getStartDateDT(): \DateTime
    {
        return (new \DateTime())->setTimestamp($this->startDate->getTimestamp());
    }

    public function setEndDate(?\DateTimeImmutable $endDate): self
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

    public function getStreamingService(): ?StreamingService
    {
        return $this->streamingService;
    }

    public function setStreamingService(?StreamingService $streamingService): self
    {
        $this->streamingService = $streamingService;

        return $this;
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

    public function getCostPerStream(): ?int
    {
        return $this->costPerStream;
    }

    public function setCostPerStream(int $costPerStream): self
    {
        $this->costPerStream = $costPerStream;

        return $this;
    }
}
