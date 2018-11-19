<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\MusicLabel;
use Faker\Generator;

final class MusicLabelFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(): MusicLabel
    {
        $musicLabel = new MusicLabel();

        $musicLabel->setName($this->faker->company);
        $musicLabel->setCreationDate(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTimeBetween('-60 years', 'now')
        ));
        $musicLabel->setCreator(\sprintf('%s %s', $this->faker->firstName, $this->faker->lastName));

        return $musicLabel;
    }

}