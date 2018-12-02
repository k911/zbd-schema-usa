<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\TrackLikeFactory;
use App\Entity\TrackLike;
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

    public function __construct(
        TrackLikeFactory $trackLikeFactory,
        EntityManagerInterface $entityManager
    )
    {
        $this->trackLikeFactory = $trackLikeFactory;
        $this->entityManager = $entityManager;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', TrackLike::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', TrackLike::class));
        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $stageTrackLike = $this->trackLikeFactory->make($entry);
            $this->entityManager->detach($entry);
            $channel->push($stageTrackLike);
            ++$counter;
            if ($counter % 100 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 100]);
            }
        }

        $channel->push('flush');
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}