<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\Release as DataWarehouseStageRelease;
use App\DataWarehouseStageRepository\MusicLabelRepository;
use App\Entity\Release;
use Cocur\Slugify\SlugifyInterface;

final class ReleaseFactory
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;
    /**
     * @var MusicLabelRepository
     */
    private $musicLabelRepository;

    public function __construct(SlugifyInterface $slugify, MusicLabelRepository $musicLabelRepository)
    {
        $this->slugify = $slugify;
        $this->musicLabelRepository = $musicLabelRepository;
    }

    public function make(Release $release): DataWarehouseStageRelease
    {
        $releaseDWS = new DataWarehouseStageRelease();
        $releaseDWS->setName($release->getName());
        $releaseDWS->setType($release->getType());
        $releaseDWS->setUpc($release->getUpc());
        $releaseDWS->setCurrency('USD');
        $releaseDWS->setOriginalPrice($release->getOriginalPrice());
        $releaseDWS->setReleaseDate($release->getReleasedAt());
        $musicLabelSlug = $this->slugify->slugify($release->getMusicLabel()->getName());
        $releaseDWS->setMusicLabel($this->musicLabelRepository->findByCanonicalName($musicLabelSlug));

        return $releaseDWS;
    }

}