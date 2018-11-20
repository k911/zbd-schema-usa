<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Artist;
use App\Random\Random;
use Faker\Generator;

final class ArtistFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $generator)
    {
        $this->faker = $generator;
    }

    public function create(): Artist
    {
        $artist = new Artist();

        $gender = $this->faker->randomElement(['male', 'female']);

        if (Random::int(0, 100) > 95) {
            $gender = 'other';
        }

        $artist->setName(\sprintf('%s %s', $this->faker->firstName($gender), $this->faker->lastName));
        $artist->setGender($gender);
        $artist->setBirthDate(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTime()
        ));

        return $artist;
    }

}