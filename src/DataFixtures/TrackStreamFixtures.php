<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\MusicLabelArtistContract;
use App\Entity\MusicLabelStreamingServiceContract;
use App\Entity\Release;
use App\Entity\Track;
use App\Entity\TrackStream;
use App\Factory\TrackStreamFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Generator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class TrackStreamFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 500000;
    public const CHUNK_SIZE = 20000;
    public const MAX_PER_TRACK = 10;
    private const CONTRACTS_CHUNK = 200;
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
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);
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
                    $progressBar->advance();
//            $this->addReference(\sprintf('release-like-%d', self::$releaseLikesCount), $like);

                    if (self::$trackStreamsCount === self::MAX_COUNT) {
                        break 3;
                    }

                    ++$chunkCounter;
                    if ($chunkCounter === self::CHUNK_SIZE) {
                        $chunkCounter = 0;
                        $manager->flush();
                        $manager->clear(TrackStream::class);
                    }
                }
            }

            if($r % self::CONTRACTS_CHUNK === 0) {
                $manager->flush();
                $manager->clear(MusicLabelStreamingServiceContract::class);
                $manager->clear(TrackStream::class);
                $manager->clear(Release::class);
                $manager->clear(Track::class);
                $manager->clear(Customer::class);
            }
        }

        $progressBar->setMaxSteps(self::$trackStreamsCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$trackStreamsCount);
    }

    public function getRandomMusicLabelTracks(int $labelId): Generator
    {
        $releaseIds = ReleaseFixtures::$musicLabelReleases[$labelId] ?? [];
        foreach ($releaseIds as $releaseId) {
            $tracks = TrackFixtures::$releaseTracks[$releaseId] ?? [];
            foreach ($tracks as $trackId) {
                if (Random::int(0, 10) > 7) {
                    yield $this->getReference(\sprintf('track-%d', $trackId));
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
