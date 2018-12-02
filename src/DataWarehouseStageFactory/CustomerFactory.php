<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\Customer as DataWarehouseStageCustomer;
use App\Entity\Customer;
use App\Random\Random;

final class CustomerFactory
{

    public function make(Customer $customer): DataWarehouseStageCustomer
    {
        $stageCustomer = new DataWarehouseStageCustomer();
        $stageCustomer->setBirthDate($customer->getJoinedAt()); // TODO: CHANGE
        $stageCustomer->setJoinedAt($customer->getJoinedAt());
        $stageCustomer->setEmail($customer->getEmail());
        $stageCustomer->setName($customer->getName());
        $stageCustomer->setCity($customer->getCity());
        $stageCustomer->setCountry($customer->getCountry());
        $stageCustomer->setGender(Random::int(0, 1) ? 'male' : 'female'); // TODO: CHANGE

        return $stageCustomer;
    }

}