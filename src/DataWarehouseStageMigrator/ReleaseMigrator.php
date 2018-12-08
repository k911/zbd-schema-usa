<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\ReleaseFactory;
use App\DataWarehouseStageRepository\ReleaseRepository;
use App\Entity\Release;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class ReleaseMigrator
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
        ReleaseFactory $releaseFactory,
        ReleaseRepository $releaseRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->releaseFactory = $releaseFactory;
        $this->entityManager = $entityManager;
        $this->releaseRepository = $releaseRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $upcRegistry = [];
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', Release::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', Release::class));

        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {

            $upc = $entry->getUpc();
            if (
                !isset($upcRegistry[$upc]) &&
                !$this->releaseRepository->existByUpc($upc)) {
                $upcRegistry[$upc] = true;
                $stageRelease = $this->releaseFactory->make($entry);
                $channel->push($stageRelease);
                $this->entityManager->detach($entry);
            }
            ++$counter;
            if ($counter % 100 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 100]);
                $channel->push('flush');
            }
        }

        $channel->push('flush');
        unset($upcRegistry);
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}