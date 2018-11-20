<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\MusicLabel;
use App\Entity\MusicLabelStreamingServiceContract;
use App\Entity\StreamingService;
use App\Random\Random;
use Faker\Generator;

final class MusicLabelStreamingServiceContractFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(MusicLabel $label, StreamingService $streamingService): MusicLabelStreamingServiceContract
    {
        $contract = new MusicLabelStreamingServiceContract();
        $contract->setMusicLabel($label);
        $contract->setStreamingService($streamingService);

        // 1-1000 ؉
        $costPerStream = Random::int(5, 100);

        $randomizer = Random::int(0, 100);
        if ($randomizer > 95) {
            $costPerStream = Random::int(1, 300);
        } elseif ($randomizer > 60) {
            $costPerStream = Random::int(10, 150);
        }

        $contract->setCostPerStream($costPerStream);

        $startDate = $this->faker->dateTimeThisCentury('-1 days');
        $contract->setStartDate(\DateTimeImmutable::createFromMutable($startDate));
        if (Random::int(0, 100) > 30) {
            $endDate = $this->faker->dateTimeBetween($startDate);
            $contract->setEndDate(\DateTimeImmutable::createFromMutable($endDate));
        }

        return $contract;
    }

}