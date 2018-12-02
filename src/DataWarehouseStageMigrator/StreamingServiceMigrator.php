<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\StreamingServiceFactory;
use App\DataWarehouseStageRepository\StreamingServiceRepository;
use App\Entity\StreamingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class StreamingServiceMigrator
{
    /**
     * @var StreamingServiceFactory
     */
    private $streamingServiceFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var StreamingServiceRepository
     */
    private $streamingServiceRepository;

    public function __construct(
        StreamingServiceFactory $streamingServiceFactory,
        StreamingServiceRepository $streamingServiceRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->streamingServiceFactory = $streamingServiceFactory;
        $this->entityManager = $entityManager;
        $this->streamingServiceRepository = $streamingServiceRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', StreamingService::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', StreamingService::class));
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $streamingService = $this->streamingServiceFactory->make($entry);
            $this->entityManager->detach($entry);
            if (!$this->streamingServiceRepository->existByCanonicalName($streamingService->getCanonicalName())) {
                $channel->push($streamingService);
            }
            $progressBarChannel->push(['inc', $progressBarNo, 1]);
        }

        $this->entityManager->clear();
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}