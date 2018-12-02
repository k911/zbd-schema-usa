<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\MusicLabel as DataWarehouseStageMusicLabel;
use App\Entity\Artist;
use App\Entity\MusicLabel;
use Cocur\Slugify\SlugifyInterface;

final class MusicLabelFactory
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function make(MusicLabel $label): DataWarehouseStageMusicLabel
    {
        $musicLabelDWS = new DataWarehouseStageMusicLabel();
        $musicLabelDWS->setCanonicalName($this->slugify->slugify($label->getName()));
        $musicLabelDWS->setName($label->getName());
        $musicLabelDWS->setCreator($label->getCreator());
        $musicLabelDWS->setCreationDate($label->getCreationDate());

        return $musicLabelDWS;
    }

}