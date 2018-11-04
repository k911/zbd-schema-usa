<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Artist;
use App\Entity\MusicLabel;
use App\Entity\MusicLabelArtistContract;
use App\Random\Random;
use Faker\Generator;

final class MusicLabelArtistContractFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(MusicLabel $label, Artist $artist): MusicLabelArtistContract
    {
        $contract = new MusicLabelArtistContract();
        $contract->setMusicLabel($label);
        $contract->setArtist($artist);

        $startDate = $this->faker->dateTimeThisCentury('-1 days');
        $contract->setStartDate(\DateTimeImmutable::createFromMutable($startDate));
        if (Random::int(0, 100) > 30) {
            $endDate = $this->faker->dateTimeBetween($startDate);
            $contract->setEndDate(\DateTimeImmutable::createFromMutable($endDate));
        }

        return $contract;
    }

}