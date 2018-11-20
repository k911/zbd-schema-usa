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
    public const EDIT_TYPES = [
        'original',
        'radio',
        'instrumental',
        'preview',
        'cover',
        'remix',
        'mix'
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
     * @ORM\Column(type="string", length=255)
     */
    private $edit;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Release", inversedBy="tracks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicRelease;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Artist")
     */
    private $artists;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TrackLike", mappedBy="track")
     */
    private $trackLikes;

    public function __construct()
    {
        $this->artists = new ArrayCollection();
        $this->trackLikes = new ArrayCollection();
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

    public function getEdit(): ?string
    {
        return $this->edit;
    }

    public function setEdit(string $edit): self
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

    /**
     * @return Collection|TrackLike[]
     */
    public function getTrackLikes(): Collection
    {
        return $this->trackLikes;
    }

    public function addTrackLike(TrackLike $trackLike): self
    {
        if (!$this->trackLikes->contains($trackLike)) {
            $this->trackLikes[] = $trackLike;
            $trackLike->setTrack($this);
        }

        return $this;
    }

    public function removeTrackLike(TrackLike $trackLike): self
    {
        if ($this->trackLikes->contains($trackLike)) {
            $this->trackLikes->removeElement($trackLike);
            // set the owning side to null (unless already changed)
            if ($trackLike->getTrack() === $this) {
                $trackLike->setTrack(null);
            }
        }

        return $this;
    }
}
