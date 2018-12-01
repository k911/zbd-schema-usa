<?php

namespace App\DataFixtures;

use App\Factory\StreamingServiceFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class StreamingServiceFixtures extends Fixture
{
    // https://en.wikipedia.org/wiki/Comparison_of_on-demand_music_streaming_services
    public const STREAMING_SERVICES = [
        ['name' => 'Spotify'],
        ['name' => 'Apple Music'],
        ['name' => 'Deezer'],
        ['name' => 'Deutsche Grammophon'],
        ['name' => 'Beatport'],
        ['name' => 'Digitally Imported Radio'],
        ['name' => 'Pandora'],
        ['name' => 'Soundcloud'],
        ['name' => 'Tidal'],
        ['name' => '8tracks'],
        ['name' => 'Amazon Prime Music'],
        ['name' => 'Amazon Music Unlimited'],
        ['name' => 'Google Play Music'],
    ];
    /**
     * @var StreamingServiceFactory
     */
    private $factory;

    public function __construct(StreamingServiceFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $i = 1;
        foreach (self::STREAMING_SERVICES as ['name' => $name]) {
            $service = $this->factory->create($name);
            $manager->persist($service);
            $this->addReference(\sprintf('streaming-service-%d', $i), $service);
            ++$i;
        }
        $manager->flush();
        echo \sprintf("Total: %d\n", \count(self::STREAMING_SERVICES));
    }
}
