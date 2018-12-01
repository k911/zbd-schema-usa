<?php

namespace App\DataFixtures;

use App\Entity\MusicLabel;
use App\Entity\Release;
use App\Factory\ReleaseFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ReleaseFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 10000;
    public const MAX_COUNT_PER_MUSIC_LABEL = 100;
    public const CHUNK_SIZE = 2500;
    public const MAX_STREAMING_RIGHTS = 30;

    /**
     * @var ReleaseFactory
     */
    private $factory;

    private $upcs;

    public static $releaseFixturesCount = 0;
    public static $musicLabelReleases = [];

    public function __construct(ReleaseFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);

        for ($l = 1; $l <= MusicLabelFixtures::COUNT; ++$l) {

            /** @var MusicLabel $musicLabel */
            $musicLabel = $this->getReference(\sprintf('music-label-%d', $l));

            $count = Random::int(1, self::MAX_COUNT_PER_MUSIC_LABEL);
            for ($i = 1; $i <= $count; ++$i) {
                $release = $this->factory->create($this->getUpc(), $musicLabel, $this->getRandomStreamingRights());
                $manager->persist($release);
                ++self::$releaseFixturesCount;
                $progressBar->advance();
                $this->addReference(\sprintf('release-%d', self::$releaseFixturesCount), $release);

                self::$musicLabelReleases[$l][] = self::$releaseFixturesCount;

                if (self::$releaseFixturesCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                    $manager->clear(Release::class);
                }
            }
        }

        $progressBar->setMaxSteps(self::$releaseFixturesCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$releaseFixturesCount);
    }

    private function getRandomStreamingRights(): iterable
    {
        $count = Random::int(1, self::MAX_STREAMING_RIGHTS);

        foreach (Random::ints($count, 1, CountryFixtures::$countryFixturesCount) as $int) {
            yield $this->getReference(\sprintf('country-%d', $int));
        }
    }

    private function getUpc(): int
    {
        do {
            $upc = Random::int(0, 99999999999999);
        } while (isset($this->upcs[$upc]));

        $this->upcs[$upc] = true;
        return $upc;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            MusicLabelFixtures::class,
            CountryFixtures::class,
        ];
    }
}
