<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer;
use App\Entity\Track;
use App\Entity\TrackLike;
use Faker\Generator;

final class TrackLikeFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(Track $track, Customer $customer): TrackLike
    {
        $trackLike = new TrackLike();
        $trackLike->setTrack($track);
        $trackLike->setCustomer($customer);
        $trackLike->setSource($this->faker->randomElement([
            'facebook',
            'instagram',
            'website',
            'artist-page',
            'release-page',
            'streaming-service',
        ]));
        $trackLike->setAddedAt(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeThisDecade
        ));
        $trackLike->setCustomerIp($this->faker->ipv4);
        return $trackLike;
    }

}