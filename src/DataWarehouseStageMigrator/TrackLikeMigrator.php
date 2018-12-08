<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\TrackLikeFactory;
use App\Entity\TrackLike;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class TrackLikeMigrator
{
    /**
     * @var TrackLikeFactory
     */
    private $trackLikeFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityManagerInterface
     */
    private $stagingEntityManager;

    public function __construct(
        TrackLikeFactory $trackLikeFactory,
        EntityManagerInterface $entityManager,
        EntityManagerInterface $stagingEntityManager
    )
    {
        $this->trackLikeFactory = $trackLikeFactory;
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
    }

    public function migrate(Channel $progressBarChannel, int $progressBarNo): void
    {
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', TrackLike::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', TrackLike::class));
        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $stageTrackLike = $this->trackLikeFactory->make($entry);
            $this->entityManager->detach($entry);
            ++$counter;
            $this->stagingEntityManager->persist($stageTrackLike);
            if ($counter % 100 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 100]);
            }

            if ($counter % 5000 === 0) {
                $this->stagingEntityManager->flush();
                $this->stagingEntityManager->clear();
            }
        }

        $this->stagingEntityManager->flush();
        $this->stagingEntityManager->clear();
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}