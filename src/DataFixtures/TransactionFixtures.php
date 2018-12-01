<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Factory\TransactionFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 10000;
    public const CHUNK_SIZE = 1000;
    public const MAX_PER_CUSTOMER = 10;
    public static $transactionsCount = 0;

    /**
     * @var TransactionFactory
     */
    private $factory;

    public function __construct(TransactionFactory $factory)
    {
        $this->factory = $factory;
    }

    public function load(ObjectManager $manager): void
    {
        $chunkCounter = 0;

        for ($r = 1; $r <= CustomerFixtures::COUNT; ++$r) {
            /** @var Customer $customer */
            $customer = $this->getReference(\sprintf(\sprintf('customer-%d', $r)));

            $count = Random::int(0, self::MAX_PER_CUSTOMER);
            for ($i = 1; $i <= $count; ++$i) {

                $transaction = $this->factory->create($customer);
                $manager->persist($transaction);
                ++self::$transactionsCount;
                $this->addReference(\sprintf('transaction-%d', self::$transactionsCount), $transaction);

                if (self::$transactionsCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                }
            }

            $manager->flush();
        }

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
        ];
    }
}
