<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TransactionRepository")
 */
class Transaction
{
    public const STATUS_COMPLETED = 'completed';

    public const STATUSES = [
        'new',
        self::STATUS_COMPLETED,
        'in-progress',
        'cancelled',
    ];

    public const PROVIDERS = [
        'mastercard',
        'visa',
        'cash',
        'payu',
        'paypal',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $provider;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $finishedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReleaseOrder", mappedBy="transaction", orphanRemoval=true)
     */
    private $releaseOrders;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Customer", inversedBy="transactions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $customer;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $customerIp;

    public function __construct()
    {
        $this->releaseOrders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isCompleted(): bool
    {
        return self::STATUS_COMPLETED === $this->status;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeImmutable
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTimeImmutable $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    /**
     * @return Collection|ReleaseOrder[]
     */
    public function getReleaseOrders(): Collection
    {
        return $this->releaseOrders;
    }

    public function addReleaseOrder(ReleaseOrder $releaseOrder): self
    {
        if (!$this->releaseOrders->contains($releaseOrder)) {
            $this->releaseOrders[] = $releaseOrder;
            $releaseOrder->setTransaction($this);
        }

        return $this;
    }

    public function removeReleaseOrder(ReleaseOrder $releaseOrder): self
    {
        if ($this->releaseOrders->contains($releaseOrder)) {
            $this->releaseOrders->removeElement($releaseOrder);
            // set the owning side to null (unless already changed)
            if ($releaseOrder->getTransaction() === $this) {
                $releaseOrder->setTransaction(null);
            }
        }

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

    public function getCustomerIp(): ?string
    {
        return $this->customerIp;
    }

    public function setCustomerIp(string $customerIp): self
    {
        $this->customerIp = $customerIp;

        return $this;
    }

    public function getCreatedAtDT(): \DateTime
    {
        if (null === $this->createdAt) {
            return new \DateTime('now');
        }

        return (new \DateTime())->setTimestamp($this->createdAt->getTimestamp());
    }

    public function getFinishedAtDT(): \DateTime
    {
        if (null === $this->finishedAt) {
            return new \DateTime('now');
        }

        return (new \DateTime())->setTimestamp($this->finishedAt->getTimestamp());
    }
}
