<?php

namespace App\DataWarehouseStage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\DataWarehouseStageRepository\CustomerRepository")
 */
class Customer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $country;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $joinedAt;

    /**
     * @ORM\Column(type="date_immutable")
     */
    private $birthDate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gender;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $city;

    /**
     * @ORM\OneToMany(targetEntity="App\DataWarehouseStage\TrackStream", mappedBy="customer")
     */
    private $trackStreams;

    public function __construct()
    {
        $this->trackStreams = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getJoinedAt(): ?\DateTimeImmutable
    {
        return $this->joinedAt;
    }

    public function setJoinedAt(\DateTimeImmutable $joinedAt): self
    {
        $this->joinedAt = $joinedAt;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeImmutable
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeImmutable $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection|TrackStream[]
     */
    public function getTrackStreams(): Collection
    {
        return $this->trackStreams;
    }

    public function addTrackStream(TrackStream $trackStream): self
    {
        if (!$this->trackStreams->contains($trackStream)) {
            $this->trackStreams[] = $trackStream;
            $trackStream->setCustomer($this);
        }

        return $this;
    }

    public function removeTrackStream(TrackStream $trackStream): self
    {
        if ($this->trackStreams->contains($trackStream)) {
            $this->trackStreams->removeElement($trackStream);
            // set the owning side to null (unless already changed)
            if ($trackStream->getCustomer() === $this) {
                $trackStream->setCustomer(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}
