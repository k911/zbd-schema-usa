<?php

namespace App\DataFixtures;

use App\Factory\MusicLabelFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class MusicLabelFixtures extends Fixture
{
    public const COUNT = 100;

    /**
     * @var MusicLabelFactory
     */
    private $factory;

    public function __construct(MusicLabelFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $musicLabel = $this->factory->create();
            $manager->persist($musicLabel);
//            $this->addReference(\sprintf('music-label-%d', $i), $musicLabel);
        }

        $manager->flush();
    }
}
