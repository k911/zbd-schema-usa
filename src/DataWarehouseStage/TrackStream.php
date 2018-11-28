<?php

namespace App\DataWarehouseStage;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrackStreamRepository")
 */
class TrackStream
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\DataWarehouseStage\Track")
     * @ORM\JoinColumn(nullable=false)
     */
    private $track;

    /**
     * @ORM\ManyToOne(targetEntity="App\DataWarehouseStage\StreamingService")
     * @ORM\JoinColumn(nullable=false)
     */
    private $streamingService;

    /**
     * @ORM\ManyToOne(targetEntity="App\DataWarehouseStage\Customer", inversedBy="trackStreams")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $startedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $customerIp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $quality;

    /**
     * @ORM\Column(type="datetime_immutable",nullable=true)
     */
    private $endedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTrack(): ?Track
    {
        return $this->track;
    }

    public function setTrack(?Track $track): self
    {
        $this->track = $track;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getCustomerIp(): ?string
    {
        return $this->customerIp;
    }

    public function setCustomerIp(string $customerIp): self
    {
        $this->customerIp = $customerIp;

        return $this;
    }

    public function getQuality(): ?string
    {
        return $this->quality;
    }

    public function setQuality(string $quality): self
    {
        $this->quality = $quality;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): self
    {
        $this->endedAt = $endedAt;

        return $this;
    }
}
