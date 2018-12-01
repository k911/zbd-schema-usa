<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Release;
use App\Entity\ReleaseLike;
use App\Factory\ReleaseLikeFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ReleaseLikeFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 50000;
    public const CHUNK_SIZE = 20000;
    public const MAX_PER_RELEASE = 10;
    private const RELEASES_CHUNK = 2000;
    public static $releaseLikesCount = 0;

    /**
     * @var ReleaseLikeFactory
     */
    private $factory;

    public function __construct(ReleaseLikeFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);

        $chunkCounter = 0;

        for ($r = 1; $r <= ReleaseFixtures::$releaseFixturesCount; ++$r) {
            /** @var Release $release */
            $release = $this->getReference(\sprintf(\sprintf('release-%d', $r)));

            $count = Random::int(0, self::MAX_PER_RELEASE);
            for ($i = 1; $i <= $count; ++$i) {
                /** @var Customer $customer */
                $customer = $this->getReference(\sprintf(\sprintf('customer-%d', Random::int(1, CustomerFixtures::COUNT))));

                $like = $this->factory->create($release, $customer);
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
                    $manager->clear(ReleaseLike::class);
                }
            }

            if ($r % self::RELEASES_CHUNK === 0) {
                $manager->flush();
                $manager->clear(ReleaseLike::class);
                $manager->clear(Release::class);
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
            ReleaseFixtures::class,
            CustomerFixtures::class,
        ];
    }
}
