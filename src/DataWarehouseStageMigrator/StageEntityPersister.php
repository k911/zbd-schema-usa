<?php
declare(strict_types=1);

namespace App\DataWarehouseStageMigrator;

use Doctrine\ORM\EntityManagerInterface;
use Swoole\Coroutine\Channel;
use Symfony\Component\Console\Style\SymfonyStyle;

final class StageEntityPersister
{
    public const CHUNK_SIZE = 5000;

    /**
     * @var EntityManagerInterface
     */
    private $stagingEntityManager;

    public function __construct(EntityManagerInterface $stagingEntityManager)
    {
        $this->stagingEntityManager = $stagingEntityManager;
    }

    public function run(Channel $channel, SymfonyStyle $io): void
    {
        $migrated = [
            \App\DataWarehouseStage\Artist::class => 0,
            \App\DataWarehouseStage\Customer::class => 0,
            \App\DataWarehouseStage\MusicLabel::class => 0,
            \App\DataWarehouseStage\Release::class => 0,
            \App\DataWarehouseStage\StreamingService::class => 0,
            \App\DataWarehouseStage\Track::class => 0,
            \App\DataWarehouseStage\TrackLike::class => 0,
            \App\DataWarehouseStage\TrackStream::class => 0,
            \App\DataWarehouseStage\Transaction::class => 0,
        ];
        $counter = 0;
        while (false !== $data = $channel->pop()) {
//            dump('adding');
            $this->stagingEntityManager->persist($data);
            ++$counter;
            ++$migrated[\get_class($data)];

            if ($counter % self::CHUNK_SIZE === 0) {
                $this->stagingEntityManager->flush();
                $this->stagingEntityManager->clear();
            }
        }

        $this->stagingEntityManager->flush();
        $this->stagingEntityManager->clear();

        $rows = [];
        foreach ($migrated as $entity => $count) {
            $rows[] = [$entity, $count];
        }

        $io->table(['Entity class', 'Migrations count'], $rows);
    }

}