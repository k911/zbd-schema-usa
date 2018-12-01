<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Release;
use App\Entity\ReleaseOrder;
use App\Entity\Transaction;
use App\Random\Random;
use Faker\Generator;

final class ReleaseOrderFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(Release $release, Transaction $transaction): ReleaseOrder
    {
        $releaseOrder = new ReleaseOrder();
        $releaseOrder->setTransaction($transaction);
        $releaseOrder->setMusicRelease($release);

        $price = $releaseOrder->getPrice();
        $factor = Random::int(5, 10) / 10;
        if (Random::int(0, 100) > 10) {
            $price *= $factor;
            $type = $this->faker->randomElement(['digital', 'e-store', 'store']);
        } else {
            $price *= (1.0 + $factor);
            $type = 'concert';
        }

        $releaseOrder->setPrice((int)ceil($price));
        $releaseOrder->setType($type);
        $releaseOrder->setPlacedAt(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween($transaction->getCreatedAtDT(), $transaction->getFinishedAtDT())
        ));
        return $releaseOrder;
    }

}