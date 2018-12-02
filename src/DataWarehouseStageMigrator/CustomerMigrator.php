<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\CustomerFactory;
use App\DataWarehouseStageRepository\ArtistRepository;
use App\DataWarehouseStageRepository\CustomerRepository;
use App\Entity\Customer;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class CustomerMigrator
{
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var ArtistRepository
     */
    private $customerRepository;

    public function __construct(
        CustomerFactory $customerFactory,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->customerFactory = $customerFactory;
        $this->entityManager = $entityManager;
        $this->customerRepository = $customerRepository;
    }

    public function migrate(Channel $channel, Channel $progressBarChannel, int $progressBarNo): void
    {
        $emailRegistry = [];
        $count = $this->entityManager->createQuery(\sprintf('SELECT COUNT(e) as count FROM %s e', Customer::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf('SELECT e FROM %s e', Customer::class));
        $counter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            $stageArtist = $this->customerFactory->make($entry);
            $this->entityManager->detach($entry);

            $slug = $stageArtist->getEmail();
            if (
                !isset($emailRegistry[$slug]) &&
                !$this->customerRepository->existByEmail($slug)) {
                $emailRegistry[$slug] = true;
                $channel->push($stageArtist);
            }
            ++$counter;
            if ($counter % 20 === 0) {
                $progressBarChannel->push(['inc', $progressBarNo, 20]);
            }
        }

        $channel->push('flush');
        unset($emailRegistry);
        $progressBarChannel->push(['finish', $progressBarNo]);
    }

    private function getEntries(IterableResult $result): Generator
    {
        foreach ($result as [0 => $entry]) {
            yield $entry;
        }
    }
}