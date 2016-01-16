<?php

namespace LWI\DeliveryTracking;

use \DateTime;

/**
 * Class DeliveryEvent
 */
class DeliveryEvent
{
    /**
     * @var string
     */
    protected $trackingNumber;

    /**
     * @var string
     */
    protected $internalNumber;

    /**
     * @var DateTime
     */
    protected $eventDate;

    /**
     * @var DeliveryStatus
     */
    protected $status;

    /**
     * DeliveryEvent constructor.
     *
     * @param string $trackingNumber
     * @param DateTime $eventDate
     * @param DeliveryStatus $status
     * @param null | string $internalNumber
     */
    public function __construct(
        $trackingNumber,
        DateTime $eventDate,
        DeliveryStatus $status,
        $internalNumber = null
    ) {
        $this->trackingNumber = $trackingNumber;
        $this->eventDate = $eventDate;
        $this->status = $status;
        $this->internalNumber = $internalNumber;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @return string
     */
    public function getInternalNumber()
    {
        return $this->internalNumber;
    }

    /**
     * @return DateTime
     */
    public function getEventDate()
    {
        return $this->eventDate;
    }

    /**
     * @return DeliveryStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function toArray()
    {
        return [
            'trackingNumber' => $this->getTrackingNumber(),
            'internalNumber' => $this->getInternalNumber(),
            'eventDate' => $this->getEventDate(),
            'status' => $this->getStatus()->__toString(),
        ];
    }
}
