<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer;
use App\Entity\Release;
use App\Entity\ReleaseLike;
use Faker\Generator;

final class ReleaseLikeFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(Release $release, Customer $customer): ReleaseLike
    {
        $releaseLike = new ReleaseLike();
        $releaseLike->setMusicRelease($release);
        $releaseLike->setCustomer($customer);
        $releaseLike->setSource($this->faker->randomElement(ReleaseLike::RELEASE_LIKE_TYPES));
        $releaseLike->setAddedAt(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeThisDecade
        ));
        $releaseLike->setCustomerIp($this->faker->ipv4);
        return $releaseLike;
    }

}