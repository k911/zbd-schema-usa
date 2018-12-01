<?php

namespace App\DataFixtures;

use App\Factory\MusicLabelFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class MusicLabelFixtures extends Fixture
{
    public const COUNT = 3000;
    public const CHUNK_SIZE = 1000;

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
        $progressBar = new ProgressBar(new ConsoleOutput(), self::COUNT);
        $chunkCounter = 0;
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $musicLabel = $this->factory->create();
            $manager->persist($musicLabel);
            $this->addReference(\sprintf('music-label-%d', $i), $musicLabel);
            ++$chunkCounter;
            $progressBar->advance();
            if ($chunkCounter === self::CHUNK_SIZE) {
                $chunkCounter = 0;
                $manager->flush();
            }
        }

        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::COUNT);
    }
}
