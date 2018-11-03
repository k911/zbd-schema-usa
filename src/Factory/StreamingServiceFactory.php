<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\StreamingService;
use Faker\Generator;

final class StreamingServiceFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(string $name): StreamingService
    {
        $service = new StreamingService();

        $service->setName($name);
        $service->setUrl(\sprintf('%s://%s.%s', $this->faker->randomElement(['http', 'https']), $this->parseName($name), $this->faker->tld));

        return $service;
    }

    /**
     * @param string $name
     * @return string
     */
    private function parseName(string $name): string
    {
        return \str_replace(' ', '', \strtolower($name));
    }

}