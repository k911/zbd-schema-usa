<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\Transaction as DataWarehouseStageTransaction;
use App\DataWarehouseStageRepository\CustomerRepository;
use App\DataWarehouseStageRepository\ReleaseRepository;
use App\Entity\Transaction;
use Generator;

final class TransactionFactory
{

    /**
     * @var ReleaseRepository
     */
    private $releaseRepository;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    public function __construct(ReleaseRepository $releaseRepository, CustomerRepository $customerRepository)
    {
        $this->releaseRepository = $releaseRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param Transaction $transaction
     * @return DataWarehouseStageTransaction[]
     */
    public function make(Transaction $transaction): Generator
    {
        $email = $transaction->getCustomer()->getEmail();
        $ip = $transaction->getCustomerIp();
        $provider = $transaction->getProvider();
        foreach ($transaction->getReleaseOrders() as $releaseOrder) {
            $customer = $this->customerRepository->findByEmail($email);
            $upc = $releaseOrder->getMusicRelease()->getUpc();
            $release = $this->releaseRepository->findByUpc($upc);
            $stageTransaction = new DataWarehouseStageTransaction();
            $stageTransaction->setCustomer($customer);
            $stageTransaction->setCustomerIp($ip);
            $stageTransaction->setRelease($release);
            $stageTransaction->setPrice($releaseOrder->getPrice());
            $stageTransaction->setCurrency('USD');
            $stageTransaction->setProvider($provider);
            $stageTransaction->setType($releaseOrder->getType());
            $stageTransaction->setCreatedAt($transaction->getCreatedAt());
            $stageTransaction->setFinishedAt($transaction->getFinishedAt());
            yield $stageTransaction;
        }
    }

}