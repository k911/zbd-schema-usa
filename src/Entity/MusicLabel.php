<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MusicLabelRepository")
 */
class MusicLabel
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
     * @ORM\Column(type="date_immutable")
     */
    private $creationYear;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $creator;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MusicLabelArtistContract", mappedBy="musicLabel")
     */
    private $musicLabelArtistContracts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Release", mappedBy="musicLabel")
     */
    private $releases;

    public function __construct()
    {
        $this->musicLabelArtistContracts = new ArrayCollection();
        $this->releases = new ArrayCollection();
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

    public function getCreationYear(): ?\DateTimeImmutable
    {
        return $this->creationYear;
    }

    public function setCreationYear(\DateTimeImmutable $creationYear): self
    {
        $this->creationYear = $creationYear;

        return $this;
    }

    public function getCreator(): ?string
    {
        return $this->creator;
    }

    public function setCreator(string $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|MusicLabelArtistContract[]
     */
    public function getMusicLabelArtistContracts(): Collection
    {
        return $this->musicLabelArtistContracts;
    }

    public function addMusicLabelArtistContract(MusicLabelArtistContract $musicLabelArtistContract): self
    {
        if (!$this->musicLabelArtistContracts->contains($musicLabelArtistContract)) {
            $this->musicLabelArtistContracts[] = $musicLabelArtistContract;
            $musicLabelArtistContract->setMusicLabel($this);
        }

        return $this;
    }

    public function removeMusicLabelArtistContract(MusicLabelArtistContract $musicLabelArtistContract): self
    {
        if ($this->musicLabelArtistContracts->contains($musicLabelArtistContract)) {
            $this->musicLabelArtistContracts->removeElement($musicLabelArtistContract);
            // set the owning side to null (unless already changed)
            if ($musicLabelArtistContract->getMusicLabel() === $this) {
                $musicLabelArtistContract->setMusicLabel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Release[]
     */
    public function getReleases(): Collection
    {
        return $this->releases;
    }

    public function addRelease(Release $release): self
    {
        if (!$this->releases->contains($release)) {
            $this->releases[] = $release;
            $release->setMusicLabel($this);
        }

        return $this;
    }

    public function removeRelease(Release $release): self
    {
        if ($this->releases->contains($release)) {
            $this->releases->removeElement($release);
            // set the owning side to null (unless already changed)
            if ($release->getMusicLabel() === $this) {
                $release->setMusicLabel(null);
            }
        }

        return $this;
    }
}
