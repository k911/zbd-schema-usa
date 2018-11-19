<?php

namespace App\DataFixtures;

use App\Entity\MusicLabel;
use App\Factory\ReleaseFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ReleaseFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 1000;
    public const MAX_COUNT_PER_MUSIC_LABEL = 100;
    public const CHUNK_SIZE = 1000;
    public const MAX_STREAMING_RIGHTS = 30;

    /**
     * @var ReleaseFactory
     */
    private $factory;

    private $upcs;

    public static $releaseFixturesCount = 0;

    public function __construct(ReleaseFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;

        for ($l = 1; $l <= MusicLabelFixtures::COUNT; ++$l) {

            /** @var MusicLabel $musicLabel */
            $musicLabel = $this->getReference(\sprintf('music-label-%d', $l));

            $count = Random::int(0, self::MAX_COUNT_PER_MUSIC_LABEL);
            for ($i = 1; $i <= $count; ++$i) {
                $release = $this->factory->create($this->getUpc(), $musicLabel, $this->getRandomStreamingRights());
                $manager->persist($release);
                ++self::$releaseFixturesCount;
                $this->addReference(\sprintf('release-%d', self::$releaseFixturesCount), $release);

                if (self::$releaseFixturesCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                }
            }

            $manager->flush();
        }

        $manager->flush();
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
