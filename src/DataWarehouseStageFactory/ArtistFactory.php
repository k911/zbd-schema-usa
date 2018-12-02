<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\Artist as DataWarehouseStageArtist;
use App\Entity\Artist;
use Cocur\Slugify\SlugifyInterface;

final class ArtistFactory
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function make(Artist $artist): DataWarehouseStageArtist
    {
        $stageArtist = new DataWarehouseStageArtist();
        $stageArtist->setCannonicalName($this->slugify->slugify($artist->getName()));
        $stageArtist->setName($artist->getName());

        return $stageArtist;
    }

}