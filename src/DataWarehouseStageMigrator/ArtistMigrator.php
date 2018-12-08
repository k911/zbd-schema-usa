<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\ArtistFactory;
use App\DataWarehouseStageRepository\ArtistRepository;
use App\Entity\Artist;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class ArtistMigrator
{
    /**
     * @var ArtistFactory
     */
    private $artistFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ArtistRepository
     */
    private $artistRepository;

    public function __construct(
        ArtistFactory $artistFactory,
        ArtistRepository $artistRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->artistFactory = $artistFactory;
        $this->entityManager = $entityManager;
        $this->artistRepository = $artistRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $slugRegistry = [];
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', Artist::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', Artist::class));
        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $stageArtist = $this->artistFactory->make($entry);
            $this->entityManager->detach($entry);

            $slug = $stageArtist->getCanonicalName();
            if (
                !isset($slugRegistry[$slug]) &&
                !$this->artistRepository->existByCanonicalName($slug)) {
                $slugRegistry[$slug] = true;
                $channel->push($stageArtist);
            }
            ++$counter;
            if ($counter % 100 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 100]);
                $channel->push('flush');
            }
        }

        $channel->push('flush');
        unset($slugRegistry);
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}