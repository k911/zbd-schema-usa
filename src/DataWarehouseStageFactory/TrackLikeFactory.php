<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\TrackLike as DataWarehouseStageTrackLike;
use App\DataWarehouseStageRepository\CustomerRepository;
use App\DataWarehouseStageRepository\TrackRepository;
use App\Entity\TrackLike;

final class TrackLikeFactory
{

    /**
     * @var TrackRepository
     */
    private $trackRepository;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    public function __construct(TrackRepository $trackRepository, CustomerRepository $customerRepository)
    {
        $this->trackRepository = $trackRepository;
        $this->customerRepository = $customerRepository;
    }

    public function make(TrackLike $like): DataWarehouseStageTrackLike
    {
        $isrc = $like->getTrack()->getIsrc();
        $email = $like->getCustomer()->getEmail();
        $stageLike = new DataWarehouseStageTrackLike();
        $stageLike->setTrack($this->trackRepository->findByIsrc($isrc));
        $stageLike->setCustomer($this->customerRepository->findByEmail($email));
        $stageLike->setSource($like->getSource());
        $stageLike->setCustomerIp($like->getCustomerIp());
        $stageLike->setAddedAt($like->getAddedAt());

        return $stageLike;
    }

}