<?php

namespace App\DataFixtures;

use App\Factory\CustomerFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public const COUNT = 100;
    private $factory;

    public function __construct(CustomerFactory $customerFactory)
    {
        $this->factory = $customerFactory;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= self::COUNT; ++$i) {
            $customer = $this->factory->create();
            $manager->persist($customer);
//            $this->addReference(\sprintf('customer-%d', $i), $customer);
        }

        $manager->flush();
    }
}
