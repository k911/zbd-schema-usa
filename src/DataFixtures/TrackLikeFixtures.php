<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Track;
use App\Entity\TrackLike;
use App\Factory\TrackLikeFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class TrackLikeFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 50000;
    public const CHUNK_SIZE = 20000;
    public const MAX_PER_TRACK = 10;
    private const TRACKS_CHUNK = 5000;
    public static $releaseLikesCount = 0;

    /**
     * @var TrackLikeFactory
     */
    private $factory;

    public function __construct(TrackLikeFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);
        for ($r = 1; $r <= TrackFixtures::$trackFixturesCount; ++$r) {
            /** @var Track $track */
            $track = $this->getReference(\sprintf(\sprintf('track-%d', $r)));

            $count = Random::int(0, self::MAX_PER_TRACK);
            for ($i = 1; $i <= $count; ++$i) {
                /** @var Customer $customer */
                $customer = $this->getReference(\sprintf(\sprintf('customer-%d', Random::int(1, CustomerFixtures::COUNT))));

                $like = $this->factory->create($track, $customer);
                $manager->persist($like);
                ++self::$releaseLikesCount;
                $progressBar->advance();
//            $this->addReference(\sprintf('release-like-%d', self::$releaseLikesCount), $like);

                if (self::$releaseLikesCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                    $manager->clear(TrackLike::class);
                }
            }

            if ($r % self::TRACKS_CHUNK === 0) {
                $manager->flush();
                $manager->clear(TrackLike::class);
                $manager->clear(Track::class);
                $manager->clear(Customer::class);
            }
        }

        $progressBar->setMaxSteps(self::$releaseLikesCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$releaseLikesCount);
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
