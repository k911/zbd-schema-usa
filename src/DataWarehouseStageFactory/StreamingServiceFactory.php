<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\StreamingService as DataWarehouseStageStreamingService;
use App\Entity\StreamingService;
use Cocur\Slugify\SlugifyInterface;

final class StreamingServiceFactory
{
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function make(StreamingService $streamingService): DataWarehouseStageStreamingService
    {
        $stageArtist = new DataWarehouseStageStreamingService();
        $stageArtist->setCanonicalName($this->slugify->slugify($streamingService->getName()));
        $stageArtist->setName($streamingService->getName());
        $stageArtist->setCreatedAt(new \DateTimeImmutable('now')); // TODO: CHANGE

        return $stageArtist;
    }

}