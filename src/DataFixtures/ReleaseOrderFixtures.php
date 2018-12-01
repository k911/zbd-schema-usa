<?php

namespace App\DataFixtures;

use App\Entity\Release;
use App\Entity\ReleaseOrder;
use App\Entity\Transaction;
use App\Factory\ReleaseOrderFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ReleaseOrderFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 100000;
    public const CHUNK_SIZE = 20000;
    public const MAX_PER_TRANSACTION = 10;
    private const CUSTOMERS_CHUNK = 5000;
    public static $releaseOrderCount = 0;

    /**
     * @var ReleaseOrderFactory
     */
    private $factory;

    public function __construct(ReleaseOrderFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);

        for ($r = 1; $r <= CustomerFixtures::COUNT; ++$r) {
            /** @var Transaction $transaction */
            $transaction = $this->getReference(\sprintf(\sprintf('transaction-%d', $r)));

            $count = Random::int(1, self::MAX_PER_TRANSACTION);

            $releases = Random::uniqueInts($count, 1, ReleaseFixtures::$releaseFixturesCount);

            foreach ($releases as $releaseId) {
                /** @var Release $release */
                $release = $this->getReference(\sprintf('release-%d', $releaseId));

                $releaseOrder = $this->factory->create($release, $transaction);
                $manager->persist($releaseOrder);
                ++self::$releaseOrderCount;
                $progressBar->advance();
//                $this->addReference(\sprintf('release-order-%d', self::$releaseOrderCount), $releaseOrder);

                if (self::$releaseOrderCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                    $manager->clear(ReleaseOrder::class);
                }
            }

            if ($r % self::CUSTOMERS_CHUNK === 0) {
                $manager->flush();
                $manager->clear(ReleaseOrder::class);
                $manager->clear(Transaction::class);
                $manager->clear(Release::class);
            }
        }

        $progressBar->setMaxSteps(self::$releaseOrderCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$releaseOrderCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            TransactionFixtures::class,
            ReleaseFixtures::class,
        ];
    }
}
