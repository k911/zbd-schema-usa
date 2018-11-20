<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer;
use App\Entity\MusicLabelStreamingServiceContract;
use App\Entity\Track;
use App\Entity\TrackStream;
use App\Random\Random;
use Faker\Generator;

final class TrackStreamFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(Track $track, Customer $customer, MusicLabelStreamingServiceContract $contract): TrackStream
    {
        $trackStream = new TrackStream();
        $trackStream->setContract($contract);
        $trackStream->setTrack($track);
        $trackStream->setStreamingService($contract->getStreamingService());
        $trackStream->setCustomer($customer);
        $trackStream->setBandwith(Random::int(64, 12280) * 2);
        $trackStream->setQuality($this->faker->randomElement(TrackStream::QUALITY));

        $startedAt = $this->faker->dateTimeBetween($contract->getStartDateDT(), $contract->getEndDateDT());
        $trackStream->setStartedAt(\DateTimeImmutable::createFromMutable(
            $startedAt
        ));

        if (Random::int(0, 100) > 20) {
            $endedAtMax = $startedAt->modify(\sprintf('+%d seconds', $track->getDuration()));
            $endedAt = $this->faker->dateTimeBetween($startedAt, $endedAtMax);
            $trackStream->setEndedAt(\DateTimeImmutable::createFromMutable(
                $endedAt
            ));
        }

        $trackStream->setCustomerIp($this->faker->ipv4);
        return $trackStream;
    }

}