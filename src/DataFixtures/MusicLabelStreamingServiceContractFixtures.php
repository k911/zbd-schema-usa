<?php

namespace App\DataFixtures;

use App\Entity\MusicLabel;
use App\Entity\StreamingService;
use App\Factory\MusicLabelStreamingServiceContractFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class MusicLabelStreamingServiceContractFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 10000;
    public const CHUNK_SIZE = 1000;
    public const MAX_DIFFERENT_RANGE_TRIES = 10;
    public const MAX_CONTRACTS_PER_PAIR = 20;
    public static $contractsCount = 0;
    public static $contractMusicLabel = [];

    /**
     * @var MusicLabelStreamingServiceContractFactory
     */
    private $factory;

    public function __construct(MusicLabelStreamingServiceContractFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);
        $streamingServicesCount = \count(StreamingServiceFixtures::STREAMING_SERVICES);

        $musicLabelStreamingServicePairs = Random::uniqueIntPairsDifferentSizes(
            \max(MusicLabelFixtures::COUNT, $streamingServicesCount) - 1,
            1, MusicLabelFixtures::COUNT,
            1, $streamingServicesCount);

        $chunkCounter = 0;
        foreach ($musicLabelStreamingServicePairs as [$musicLabelId, $streamingServiceId]) {

            /** @var StreamingService $streamingService */
            $streamingService = $this->getReference(\sprintf('streaming-service-%d', $streamingServiceId));

            /** @var MusicLabel $musicLabel */
            $musicLabel = $this->getReference(\sprintf('music-label-%d', $musicLabelId));

            $contractsNum = Random::int(0, self::MAX_CONTRACTS_PER_PAIR);
            $ranges = [];
            $times = 0;
            while ($contractsNum > 0) {
                $contract = $this->factory->create($musicLabel, $streamingService);
                foreach ($ranges as [$start, $end]) {
                    if ($contract->validBetween($start, $end)) {
                        ++$times;
                        if ($times === self::MAX_DIFFERENT_RANGE_TRIES) {
                            continue 3;
                        }
                        continue 2;
                    }
                }

                $times = 0;
                $ranges[] = [$contract->getStartDate(), $contract->getEndDate()];
                --$contractsNum;

                $manager->persist($contract);

                --$chunkCounter;
                ++self::$contractsCount;
                $progressBar->advance();
                $this->addReference(\sprintf('label-service-contract-%d', self::$contractsCount), $contract);
                self::$contractMusicLabel[self::$contractsCount] = $musicLabelId;

                if (self::$contractsCount === self::MAX_COUNT) {
                    break 2;
                }

                if ($chunkCounter === self::CHUNK_SIZE - 1) {
                    $manager->flush();
                    $chunkCounter = 0;
                }
            }
        }

        $progressBar->setMaxSteps(self::$contractsCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$contractsCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            StreamingServiceFixtures::class,
            MusicLabelFixtures::class,
        ];
    }
}
