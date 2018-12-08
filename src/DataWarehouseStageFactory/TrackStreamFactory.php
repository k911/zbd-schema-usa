<?php
declare(strict_types=1);

namespace App\DataWarehouseStageFactory;

use App\DataWarehouseStage\TrackStream as DataWarehouseStageTrackStream;
use App\DataWarehouseStage\Transaction as DataWarehouseStageTransaction;
use App\DataWarehouseStageRepository\CustomerRepository;
use App\DataWarehouseStageRepository\StreamingServiceRepository;
use App\DataWarehouseStageRepository\TrackRepository;
use App\Entity\TrackStream;
use Cocur\Slugify\SlugifyInterface;
use Generator;

final class TrackStreamFactory
{

    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var TrackRepository
     */
    private $trackRepository;
    /**
     * @var StreamingServiceRepository
     */
    private $streamingServiceRepository;
    /**
     * @var SlugifyInterface
     */
    private $slugify;

    public function __construct(
        SlugifyInterface $slugify,
        CustomerRepository $customerRepository,
        TrackRepository $trackRepository,
        StreamingServiceRepository $streamingServiceRepository
    )
    {
        $this->customerRepository = $customerRepository;
        $this->trackRepository = $trackRepository;
        $this->streamingServiceRepository = $streamingServiceRepository;
        $this->slugify = $slugify;
    }

    /**
     * @param TrackStream $trackStream
     * @return Generator
     */
    public function make(TrackStream $trackStream): Generator
    {
        $isrc = $trackStream->getTrack()->getIsrc();
        $email = $trackStream->getCustomer()->getEmail();
        $streamingServiceSlug = $this->slugify->slugify($trackStream->getStreamingService()->getName());

        $track = $this->trackRepository->findByIsrc($isrc);
        $customer = $this->customerRepository->findByEmail($email);
        $release = $track->getRelease();

        $costPerStream = $trackStream->getContract()->getCostPerStream();
        $originalPrice = $release->getOriginalPrice();
        $cost = (int)ceil(0.001 * $costPerStream * $originalPrice);
        $customerIp = $trackStream->getCustomerIp();

        $stageStream = new DataWarehouseStageTrackStream();
        $stageStream->setCustomer($customer);
        $stageStream->setCustomerIp($customerIp);
        $stageStream->setTrack($track);
        $stageStream->setStartedAt($trackStream->getStartedAt());
        $stageStream->setEndedAt($trackStream->getEndedAt());
        $stageStream->setStreamingService($this->streamingServiceRepository->findByCanonicalName($streamingServiceSlug));
        $stageStream->setQuality($trackStream->getQuality());
        yield $stageStream;

        $stageTransaction = new DataWarehouseStageTransaction();
        $stageTransaction->setCustomer($customer);
        $stageTransaction->setCustomerIp($customerIp);
        $stageTransaction->setRelease($release);
        $stageTransaction->setPrice($cost);
        $stageTransaction->setCurrency('USD');
        $stageTransaction->setProvider('cash');
        $stageTransaction->setType('stream');
        $stageTransaction->setCreatedAt($trackStream->getStartedAt());
        $stageTransaction->setFinishedAt($trackStream->getEndedAt());
        yield $stageTransaction;
    }

}