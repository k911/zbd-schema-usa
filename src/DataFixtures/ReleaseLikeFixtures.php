<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Release;
use App\Factory\ReleaseLikeFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ReleaseLikeFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 100000;
    public const CHUNK_SIZE = 10000;
    public const MAX_PER_RELEASE = 10;
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
//            $this->addReference(\sprintf('release-like-%d', self::$releaseLikesCount), $like);

                if (self::$releaseLikesCount === self::MAX_COUNT) {
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
