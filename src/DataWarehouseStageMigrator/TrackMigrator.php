<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\ReleaseFactory;
use App\DataWarehouseStageFactory\TrackFactory;
use App\DataWarehouseStageRepository\ReleaseRepository;
use App\DataWarehouseStageRepository\TrackRepository;
use App\Entity\Release;
use App\Entity\Track;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class TrackMigrator
{
    /**
     * @var ReleaseFactory
     */
    private $releaseFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ReleaseRepository
     */
    private $releaseRepository;

    public function __construct(
        TrackFactory $releaseFactory,
        TrackRepository $releaseRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->releaseFactory = $releaseFactory;
        $this->entityManager = $entityManager;
        $this->releaseRepository = $releaseRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $isrcRegistry = [];
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', Track::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', Track::class));

        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $isrc = $entry->getIsrc();
            if (
                !isset($isrcRegistry[$isrc]) &&
                !$this->releaseRepository->existByIsrc($isrc)) {
                $isrcRegistry[$isrc] = true;
                $stageTrack = $this->releaseFactory->make($entry);
                $channel->push($stageTrack);
                $this->entityManager->detach($entry);
            }
            ++$counter;
            if ($counter % 100 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 100]);
            }
        }

        $channel->push('flush');
        unset($isrcRegistry);
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}