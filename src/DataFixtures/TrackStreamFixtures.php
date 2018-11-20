<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\MusicLabelStreamingServiceContract;
use App\Entity\Track;
use App\Factory\TrackStreamFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;

class TrackStreamFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 10000;
    public const CHUNK_SIZE = 1000;
    public const MAX_PER_TRACK = 30;
    public static $trackStreamsCount = 0;

    /**
     * @var TrackStreamFactory
     */
    private $factory;

    public function __construct(TrackStreamFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;

        for ($r = 1; $r <= MusicLabelStreamingServiceContractFixtures::$contractsCount; ++$r) {

            /** @var MusicLabelStreamingServiceContract $contract */
            $contract = $this->getReference(\sprintf(\sprintf('label-service-contract-%d', $r)));

            /** @var Track $track */
            foreach ($this->getRandomMusicLabelTracks(MusicLabelStreamingServiceContractFixtures::$contractMusicLabel[$r]) as $track) {
                $count = Random::int(0, self::MAX_PER_TRACK);
                for ($i = 1; $i <= $count; ++$i) {
                    /** @var Customer $customer */
                    $customer = $this->getReference(\sprintf(\sprintf('customer-%d', Random::int(1, CustomerFixtures::COUNT))));

                    $trackStream = $this->factory->create($track, $customer, $contract);
                    $manager->persist($trackStream);
                    ++self::$trackStreamsCount;
//            $this->addReference(\sprintf('release-like-%d', self::$releaseLikesCount), $like);

                    if (self::$trackStreamsCount === self::MAX_COUNT) {
                        break 2;
                    }

                    ++$chunkCounter;
                    if ($chunkCounter === self::CHUNK_SIZE) {
                        $chunkCounter = 0;
                        $manager->flush();
                    }
                }
            }

            $manager->flush();
        }

        $manager->flush();
    }

    public function getRandomMusicLabelTracks(int $labelId): Generator
    {
        $releaseIds = ReleaseFixtures::$musicLabelReleases[$labelId] ?? [];
        foreach ($releaseIds as $releaseId) {
            if (Random::int(0, 3) > 1) {
                $tracks = TrackFixtures::$releaseTracks[$releaseId] ?? [];
                foreach ($tracks as $trackId) {
                    if (Random::int(0, 10) > 7) {
                        yield $this->getReference(\sprintf('track-%d', $trackId));
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            TrackFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
