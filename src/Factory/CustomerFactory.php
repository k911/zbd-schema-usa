<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer;
use Faker\Generator;

final class CustomerFactory
{
    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(): Customer
    {
        $customer = new Customer();

        $customer->setName($this->faker->name);
        $customer->setAddress($this->faker->address);
        $customer->setCity($this->faker->city);
        $customer->setCountry($this->faker->countryISOAlpha3);
        $customer->setEmail($this->faker->email);
        $customer->setJoinedAt(\DateTimeImmutable::createFromMutable(
            $this->faker->dateTime()
        ));
        $customer->setPhone($this->faker->phoneNumber);
        $customer->setPasswordHash($this->faker->sha256);

        return $customer;
    }

}