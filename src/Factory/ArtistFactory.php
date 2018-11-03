<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Artist;
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

        $artist->setName(\sprintf('%s %s', $this->faker->firstName($gender), $this->faker->lastName));
        $artist->setGender($gender);
        $artist->setBirthDate(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTime()
        ));

        return $artist;
    }

}