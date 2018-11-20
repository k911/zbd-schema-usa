<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReleaseOrderRepository")
 */
class ReleaseOrder
{
    public const TYPES = [
        'stream', //from streaming service
        'digital', //digital album in e-store
        'e-store', // via internet shop
        'classic-shop', // classic order in real shop
        'concert', // with artist signature
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Release")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicRelease;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Transaction", inversedBy="releaseOrders")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $placedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMusicRelease(): ?Release
    {
        return $this->musicRelease;
    }

    public function setMusicRelease(?Release $musicRelease): self
    {
        $this->musicRelease = $musicRelease;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPlacedAt(): ?\DateTimeImmutable
    {
        return $this->placedAt;
    }

    public function setPlacedAt(\DateTimeImmutable $placedAt): self
    {
        $this->placedAt = $placedAt;

        return $this;
    }
}
