<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Transaction;
use App\Factory\TransactionFactory;
use App\Random\Random;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class TransactionFixtures extends Fixture implements DependentFixtureInterface
{
    public const MAX_COUNT = 100000;
    public const CHUNK_SIZE = 20000;
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
        $progressBar = new ProgressBar(new ConsoleOutput(), self::MAX_COUNT);
        for ($r = 1; $r <= CustomerFixtures::COUNT; ++$r) {
            /** @var Customer $customer */
            $customer = $this->getReference(\sprintf(\sprintf('customer-%d', $r)));

            $count = Random::int(0, self::MAX_PER_CUSTOMER);
            for ($i = 1; $i <= $count; ++$i) {

                $transaction = $this->factory->create($customer);
                $manager->persist($transaction);
                ++self::$transactionsCount;
                $progressBar->advance();
                $this->addReference(\sprintf('transaction-%d', self::$transactionsCount), $transaction);

                if (self::$transactionsCount === self::MAX_COUNT) {
                    break 2;
                }

                ++$chunkCounter;
                if ($chunkCounter === self::CHUNK_SIZE) {
                    $chunkCounter = 0;
                    $manager->flush();
                    $manager->clear(Transaction::class);
                }
            }

            if($r % 1000 === 0) {
                $manager->flush();
                $manager->clear(Transaction::class);
                $manager->clear(Customer::class);
            }
        }

        $progressBar->setMaxSteps(self::$transactionsCount);
        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::$transactionsCount);
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
