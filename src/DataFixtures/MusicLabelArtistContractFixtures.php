<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\MusicLabel;
use App\Factory\MusicLabelArtistContractFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class MusicLabelArtistContractFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 10000;
    public const CHUNK_SIZE = 1000;
    public const MAX_DIFFERENT_RANGE_TRIES = 5;
    public const MAX_CONTRACTS_PER_PAIR = 10;
    public static $musicLabelArtistContractsCount = 0;

    /**
     * @var MusicLabelArtistContractFactory
     */
    private $factory;

    public function __construct(MusicLabelArtistContractFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $musicLabelArtistPairs = Random::uniqueIntPairsDifferentSizes(
            \max(MusicLabelFixtures::COUNT, ArtistFixtures::COUNT) - 1,
            1, MusicLabelFixtures::COUNT,
            1, ArtistFixtures::COUNT);

        $chunkCounter = 0;
        $counter = self::MAX_COUNT;
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);
        foreach ($musicLabelArtistPairs as [$musicLabelId, $artistId]) {

            /** @var Artist $artist */
            $artist = $this->getReference(\sprintf('artist-%d', $artistId));

            /** @var MusicLabel $musicLabel */
            $musicLabel = $this->getReference(\sprintf('music-label-%d', $musicLabelId));

            $contractsNum = Random::int(0, self::MAX_CONTRACTS_PER_PAIR);
            $ranges = [];
            $times = 0;
            while ($contractsNum > 0) {
                $contract = $this->factory->create($musicLabel, $artist);
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
                $progressBar->advance();
                ++self::$musicLabelArtistContractsCount;
                --$chunkCounter;
                --$counter;
                if ($counter === 0) {
                    break 2;
                }

                if ($chunkCounter === self::CHUNK_SIZE - 1) {
                    $manager->flush();
                    $chunkCounter = 0;
                }
            }
        }

        $progressBar->setMaxSteps(self::$musicLabelArtistContractsCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$musicLabelArtistContractsCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            ArtistFixtures::class,
            MusicLabelFixtures::class,
        ];
    }
}
