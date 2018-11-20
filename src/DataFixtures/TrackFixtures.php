<?php

namespace App\DataFixtures;

use App\Entity\Artist;
use App\Entity\Release;
use App\Entity\Track;
use App\Factory\TrackFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TrackFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 1000;
    public const MAX_COUNT_PER_RELEASE = 10;
    public const CHUNK_SIZE = 1000;
    public const MAX_ARTISTS = 3;

    /**
     * @var TrackFactory
     */
    private $factory;

    public static $trackFixturesCount = 0;

    public function __construct(TrackFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;

        $isrcGenerator = Random::uniqueAlphaNumGenerator(12, ReleaseFixtures::$releaseFixturesCount);
        $isrcGenerator->rewind();
        for ($l = 1; $l <= ReleaseFixtures::$releaseFixturesCount; ++$l) {

            /** @var Release $release */
            $release = $this->getReference(\sprintf('release-%d', $l));

            $count = Random::int(0, self::MAX_COUNT_PER_RELEASE);
            for ($i = 1; $i <= $count; ++$i) {
                $track = $this->factory->create($isrcGenerator->current(), $release);
                $isrcGenerator->next();

                $this->addRandomArtists($release, $track);

                $manager->persist($track);
                ++self::$trackFixturesCount;
                $this->addReference(\sprintf('track-%d', self::$trackFixturesCount), $track);

                if (self::$trackFixturesCount === self::MAX_COUNT) {
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

    public function addRandomArtists(Release $release, Track $track): void
    {
        $artists = $release->getMusicLabel()->getArtistsForDate($release->getReleasedAt());
        $max = \count($artists);
        $count = min($max, Random::int(1, self::MAX_ARTISTS));

        $added = false;
        foreach (Random::ints($count, 0, $max - 1) as $index) {
            $track->addArtist($artists[$index]);
            $added = true;
        }

        if (!$added) {
            /** @var Artist $artist */
            $artist = $this->getReference('artist-various-artists');
            $track->addArtist($artist);
        }
    }

    /**
     * {@inheritdoc}
     */
    public
    function getDependencies(): array
    {
        return [
            ReleaseFixtures::class,
            ArtistFixtures::class,
        ];
    }
}
