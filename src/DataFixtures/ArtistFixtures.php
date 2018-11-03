<?php

namespace App\DataFixtures;

use App\Factory\ArtistFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArtistFixtures extends Fixture
{
    public const COUNT = 100;
    private $artistFactory;

    public function __construct(ArtistFactory $artistFactory)
    {
        $this->artistFactory = $artistFactory;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $artist = $this->artistFactory->create();
            $manager->persist($artist);
//            $this->addReference(\sprintf('artist-%d', $i), $artist);
        }

        $manager->flush();
    }
}
