<?php

namespace LWI\DeliveryTracking;

/**
 * Class DeliveryTracking
 */
class DeliveryTracking
{
    /**
     * @var DeliveryServiceInterface
     */
    protected $deliveryService;

    /**
     * DeliveryTracking constructor.
     * @param DeliveryServiceInterface $deliveryService
     */
    public function __construct(DeliveryServiceInterface $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * @param string $trackingNumber
     *
     * @return DeliveryStatus
     */
    public function getDeliveryStatus($trackingNumber)
    {
        return $this->deliveryService->getDeliveryStatus($trackingNumber);
    }

    /**
     * @param string $reference
     *
     * @return DeliveryStatus
     */
    public function getDeliveryStatusByInternalReference($reference)
    {
        $trackingNumber = $this->deliveryService->getTrackingNumberByInternalReference($reference);
        return $this->getDeliveryStatus($trackingNumber);
    }

    /**
     * @param string $trackingNumber
     *
     * @return DeliveryEvent
     */
    public function getLastEvent($trackingNumber)
    {
        return $this->deliveryService->getLastEvent($trackingNumber);
    }


    /**
     * @param $reference
     * @return string
     */
    public function getTrackingNumberByInternalReference($reference)
    {
        return $this->deliveryService->getTrackingNumberByInternalReference($reference);
    }
}
