<?php

namespace App\DataFixtures;

use App\Factory\CustomerFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class CustomerFixtures extends Fixture
{
    public const COUNT = 10000;
    public const CHUNK_SIZE = 5000;
    private $factory;

    public function __construct(CustomerFactory $customerFactory)
    {
        $this->factory = $customerFactory;
    }

    public function load(ObjectManager $manager): void
    {
        $progressBar = new ProgressBar(new ConsoleOutput(), self::COUNT);
        $chunkCounter = 0;
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $customer = $this->factory->create();
            $manager->persist($customer);
            $this->addReference(\sprintf('customer-%d', $i), $customer);
            ++$chunkCounter;
            $progressBar->advance();
            if ($chunkCounter === self::CHUNK_SIZE) {
                $chunkCounter = 0;
                $manager->flush();
            }
        }

        $manager->flush();
        $manager->clear();
        $progressBar->finish();
        echo PHP_EOL;
        echo \sprintf("Total: %d\n", self::COUNT);
    }
}
