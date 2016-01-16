<?php

namespace LWI\DeliveryTracking;

/**
 * Interface DeliveryServiceInterface
 */
interface DeliveryServiceInterface
{
    /**
     * @param string $trackingNumber
     * @return DeliveryStatus
     */
    public function getDeliveryStatus($trackingNumber);

    /**
     * @param array $trackingNumbers
     * @return array | DeliveryStatus[]
     */
    public function getDeliveryStatuses($trackingNumbers);

    /**
     * @param string $reference
     * @return string
     */
    public function getTrackingNumberByInternalReference($reference);

    /**
     * @param array $references
     * @return array
     */
    public function getTrackingNumbersByInternalReferences($references);

    /**
     * @param $trackingNumber
     * @return DeliveryEvent
     */
    public function getLastEvent($trackingNumber);

    /**
     * @param $trackingNumbers
     * @return DeliveryEvent[]
     */
    public function getLastEventForMultipleDeliveries($trackingNumbers);
}
