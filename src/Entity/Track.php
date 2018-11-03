<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrackRepository")
 */
class Track
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
    private $title;

    /**
     * @ORM\Column(type="string", length=12)
     */
    private $isrc;

    /**
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @ORM\Column(type="simple_array")
     */
    private $edit = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Release", inversedBy="tracks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicRelease;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Artist")
     */
    private $artists;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getIsrc(): ?string
    {
        return $this->isrc;
    }

    public function setIsrc(string $isrc): self
    {
        $this->isrc = $isrc;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getEdit(): ?array
    {
        return $this->edit;
    }

    public function setEdit(array $edit): self
    {
        $this->edit = $edit;

        return $this;
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

    /**
     * @return Collection|Artist[]
     */
    public function getArtists(): Collection
    {
        return $this->artists;
    }

    public function addArtist(Artist $artist): self
    {
        if (!$this->artists->contains($artist)) {
            $this->artists[] = $artist;
        }

        return $this;
    }

    public function removeArtist(Artist $artist): self
    {
        if ($this->artists->contains($artist)) {
            $this->artists->removeElement($artist);
        }

        return $this;
    }
}