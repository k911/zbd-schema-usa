<?php
declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer;
use App\Entity\Transaction;
use Faker\Generator;

final class TransactionFactory
{

    /**
     * @var Generator
     */
    private $faker;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
    }

    public function create(Customer $customer): Transaction
    {
        $transaction = new Transaction();

        $transaction->setProvider($this->faker->randomElement(Transaction::PROVIDERS));
        $transaction->setStatus($this->faker->randomElement(Transaction::STATUSES));
        $transaction->setCustomer($customer);
        $startedAt = $this->faker->dateTimeThisDecade('-3 days');
        $transaction->setCreatedAt(\DateTimeImmutable::createFromMutable($startedAt));

        if ($this->faker->boolean) {
            $updatedAtMax = clone $startedAt;
            $updatedAtMax = $updatedAtMax->modify('+2 days');

            $transaction->setUpdatedAt(\DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween($startedAt, $updatedAtMax)
            ));

            $endedAtMax = clone $updatedAtMax;
            $endedAtMax = $endedAtMax->modify('+1 days');

            $transaction->setUpdatedAt(\DateTimeImmutable::createFromMutable(
                $this->faker->dateTimeBetween($updatedAtMax, $endedAtMax)
            ));
        }

        $transaction->setCustomerIp($this->faker->ipv4);
        return $transaction;
    }

}