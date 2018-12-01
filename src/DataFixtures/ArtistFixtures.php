<?php

namespace App\DataFixtures;

use App\Factory\ArtistFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class ArtistFixtures extends Fixture
{
    public const COUNT = 50000;
    public const CHUNK_SIZE = 5000;
    private $artistFactory;

    public function __construct(ArtistFactory $artistFactory)
    {
        $this->artistFactory = $artistFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $progressBar = new ProgressBar(new ConsoleOutput(), self::COUNT);
        $chunkCounter = 0;
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $artist = $this->artistFactory->create();
            $manager->persist($artist);
            $this->addReference(\sprintf('artist-%d', $i), $artist);
            ++$chunkCounter;
            $progressBar->advance();
            if ($chunkCounter === self::CHUNK_SIZE) {
                $chunkCounter = 0;
                $manager->flush();
            }
        }

        $variousArtists = $this->artistFactory->create();
        $variousArtists->setName('Various Artists');
        $variousArtists->setGender('other');
        $this->addReference('artist-various-artists', $variousArtists);
        $manager->persist($variousArtists);

        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::COUNT);
    }
}
