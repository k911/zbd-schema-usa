<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ReleaseRepository")
 * @ORM\Table(name="music_release")
 */
class Release
{

    public const RELEASE_TYPES = [
        'single', 'album', 'digital',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="bigint")
     */
    private $upc;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\Column(type="integer")
     */
    private $originalPrice;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $cLine;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pLine;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Country")
     */
    private $streamingRights;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MusicLabel", inversedBy="releases")
     * @ORM\JoinColumn(nullable=false)
     */
    private $musicLabel;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Track", mappedBy="musicRelease")
     */
    private $tracks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ReleaseLike", mappedBy="musicRelease")
     */
    private $releaseLikes;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $releasedAt;

    public function __construct()
    {
        $this->streamingRights = new ArrayCollection();
        $this->tracks = new ArrayCollection();
        $this->releaseLikes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpc(): ?int
    {
        return $this->upc;
    }

    public function setUpc(int $upc): self
    {
        $this->upc = $upc;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOriginalPrice(): ?int
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(int $originalPrice): self
    {
        $this->originalPrice = $originalPrice;

        return $this;
    }

    public function getCLine(): ?string
    {
        return $this->cLine;
    }

    public function setCLine(string $cLine): self
    {
        $this->cLine = $cLine;

        return $this;
    }

    public function getPLine(): ?string
    {
        return $this->pLine;
    }

    public function setPLine(string $pLine): self
    {
        $this->pLine = $pLine;

        return $this;
    }

    /**
     * @return Collection|Country[]
     */
    public function getStreamingRights(): Collection
    {
        return $this->streamingRights;
    }

    public function addStreamingRight(Country $streamingRight): self
    {
        if (!$this->streamingRights->contains($streamingRight)) {
            $this->streamingRights[] = $streamingRight;
        }

        return $this;
    }

    public function removeStreamingRight(Country $streamingRight): self
    {
        if ($this->streamingRights->contains($streamingRight)) {
            $this->streamingRights->removeElement($streamingRight);
        }

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

    /**
     * @return Collection|Track[]
     */
    public function getTracks(): Collection
    {
        return $this->tracks;
    }

    public function addTrack(Track $track): self
    {
        if (!$this->tracks->contains($track)) {
            $this->tracks[] = $track;
            $track->setMusicRelease($this);
        }

        return $this;
    }

    public function removeTrack(Track $track): self
    {
        if ($this->tracks->contains($track)) {
            $this->tracks->removeElement($track);
            // set the owning side to null (unless already changed)
            if ($track->getMusicRelease() === $this) {
                $track->setMusicRelease(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ReleaseLike[]
     */
    public function getReleaseLikes(): Collection
    {
        return $this->releaseLikes;
    }

    public function addReleaseLike(ReleaseLike $releaseLike): self
    {
        if (!$this->releaseLikes->contains($releaseLike)) {
            $this->releaseLikes[] = $releaseLike;
            $releaseLike->setMusicRelease($this);
        }

        return $this;
    }

    public function removeReleaseLike(ReleaseLike $releaseLike): self
    {
        if ($this->releaseLikes->contains($releaseLike)) {
            $this->releaseLikes->removeElement($releaseLike);
            // set the owning side to null (unless already changed)
            if ($releaseLike->getMusicRelease() === $this) {
                $releaseLike->setMusicRelease(null);
            }
        }

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeImmutable
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(\DateTimeImmutable $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }
}
