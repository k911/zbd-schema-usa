<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use App\DataWarehouseStageFactory\TransactionFactory;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Generator;
use Swoole\Coroutine\Channel;

class TransactionMigrator
{
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EntityManagerInterface
     */
    private $stagingEntityManager;

    public function __construct(
        TransactionFactory $transactionFactory,
        EntityManagerInterface $stagingEntityManager,
        EntityManagerInterface $entityManager
    )
    {
        $this->transactionFactory = $transactionFactory;
        $this->entityManager = $entityManager;
        $this->stagingEntityManager = $stagingEntityManager;
    }

    public function migrate(Channel $progressBarChannel, int $progressBarNo): void
    {
        $count = $this->entityManager->createQuery(\sprintf("SELECT COUNT(e) as count FROM %s e WHERE e.status = 'completed'", Transaction::class))->getResult()[0]['count'];

        $progressBarChannel->push(['set_max', $progressBarNo, (int)$count]);

        $entityCollectionQuery = $this->entityManager->createQuery(\sprintf("SELECT e FROM %s e WHERE e.status = 'completed'", Transaction::class));
        $counter = 0;
        $flushCounter = 0;
        foreach ($this->getEntries($entityCollectionQuery->iterate()) as $entry) {
            foreach ($this->transactionFactory->make($entry) as $transaction) {
                $this->entityManager->detach($entry);
                $this->stagingEntityManager->persist($transaction);
                ++$counter;
                ++$flushCounter;
                if ($counter % 100 === 0) {
                    $progressBarChannel->push(['inc', $progressBarNo, 100]);
                }
            }

            if ($flushCounter > 5000) {
                $flushCounter = 0;
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