<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StreamingServiceRepository")
 */
class StreamingService
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MusicLabelStreamingServiceContract", mappedBy="streamingService")
     */
    private $musicLabelStreamingServiceContracts;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $createdAt;

    public function __construct()
    {
        $this->musicLabelStreamingServiceContracts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection|MusicLabelStreamingServiceContract[]
     */
    public function getMusicLabelStreamingServiceContracts(): Collection
    {
        return $this->musicLabelStreamingServiceContracts;
    }

    public function addMusicLabelStreamingServiceContract(MusicLabelStreamingServiceContract $musicLabelStreamingServiceContract): self
    {
        if (!$this->musicLabelStreamingServiceContracts->contains($musicLabelStreamingServiceContract)) {
            $this->musicLabelStreamingServiceContracts[] = $musicLabelStreamingServiceContract;
            $musicLabelStreamingServiceContract->setStreamingService($this);
        }

        return $this;
    }

    public function removeMusicLabelStreamingServiceContract(MusicLabelStreamingServiceContract $musicLabelStreamingServiceContract): self
    {
        if ($this->musicLabelStreamingServiceContracts->contains($musicLabelStreamingServiceContract)) {
            $this->musicLabelStreamingServiceContracts->removeElement($musicLabelStreamingServiceContract);
            // set the owning side to null (unless already changed)
            if ($musicLabelStreamingServiceContract->getStreamingService() === $this) {
                $musicLabelStreamingServiceContract->setStreamingService(null);
            }
        }

        return $this;
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
}
