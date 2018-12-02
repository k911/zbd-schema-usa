<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\Track as DataWarehouseStageTrack;
use App\DataWarehouseStageRepository\ArtistRepository;
use App\DataWarehouseStageRepository\ReleaseRepository;
use App\Entity\Track;
use Cocur\Slugify\SlugifyInterface;

final class TrackFactory
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;
    /**
     * @var ReleaseRepository
     */
    private $releaseRepository;
    /**
     * @var ArtistRepository
     */
    private $artistRepository;

    public function __construct(SlugifyInterface $slugify, ReleaseRepository $releaseRepository, ArtistRepository $artistRepository)
    {
        $this->slugify = $slugify;
        $this->releaseRepository = $releaseRepository;
        $this->artistRepository = $artistRepository;
    }

    public function make(Track $track): DataWarehouseStageTrack
    {

        $warehouseTrack = new DataWarehouseStageTrack();
        $warehouseTrack->setRelease($this->releaseRepository->findByUpc($track->getMusicRelease()->getUpc()));

        foreach ($track->getArtists() as $artist) {
            $slug = $this->slugify->slugify($artist->getName());
            $warehouseTrack->addArtist($this->artistRepository->findByCanonicalName($slug));
        }

        $warehouseTrack->setDuration($track->getDuration());
        $warehouseTrack->setEdit($track->getEdit());
        $warehouseTrack->setTitle($track->getTitle());
        $warehouseTrack->setCreatedAt(new \DateTimeImmutable('now')); // TODO: CHANGE
        $warehouseTrack->setIsrc($track->getIsrc());

        return $warehouseTrack;
    }

}