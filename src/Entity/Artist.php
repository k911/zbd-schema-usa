<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArtistRepository")
 */
class Artist
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
     * @ORM\Column(type="integer", options={"unsigned"=true})
     */
    private $age;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sex;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MusicLabelArtistContract", mappedBy="artist")
     */
    private $musicLabelArtistContracts;

    public function __construct()
    {
        $this->musicLabelArtistContracts = new ArrayCollection();
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

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

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
            $musicLabelArtistContract->setArtist($this);
        }

        return $this;
    }

    public function removeMusicLabelArtistContract(MusicLabelArtistContract $musicLabelArtistContract): self
    {
        if ($this->musicLabelArtistContracts->contains($musicLabelArtistContract)) {
            $this->musicLabelArtistContracts->removeElement($musicLabelArtistContract);
            // set the owning side to null (unless already changed)
            if ($musicLabelArtistContract->getArtist() === $this) {
                $musicLabelArtistContract->setArtist(null);
            }
        }

        return $this;
    }
}
