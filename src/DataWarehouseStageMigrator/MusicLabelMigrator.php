<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\MusicLabelFactory;
use App\DataWarehouseStageRepository\ArtistRepository;
use App\DataWarehouseStageRepository\MusicLabelRepository;
use App\Entity\MusicLabel;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class MusicLabelMigrator
{
    /**
     * @var MusicLabelFactory
     */
    private $musicLabelFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ArtistRepository
     */
    private $artistRepository;

    public function __construct(
        MusicLabelFactory $musicLabelFactory,
        MusicLabelRepository $musicLabelRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->musicLabelFactory = $musicLabelFactory;
        $this->entityManager = $entityManager;
        $this->artistRepository = $musicLabelRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $slugRegistry = [];
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', MusicLabel::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', MusicLabel::class));
        foreach ($this->getEntries($entityCollectionQuery->iterate(null, AbstractQuery::HYDRATE_SIMPLEOBJECT)) as $entry) {
            $musicLabel = $this->musicLabelFactory->make($entry);
            $this->entityManager->detach($entry);

            $slug = $musicLabel->getCanonicalName();
            if (
                !isset($slugRegistry[$slug]) &&
                !$this->artistRepository->existByCanonicalName($slug)) {
                $slugRegistry[$slug] = true;
                $channel->push($musicLabel);
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