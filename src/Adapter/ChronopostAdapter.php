<?php

namespace LWI\DeliveryTracking\Adapter;

use LWI\DeliveryTracking\Behavior\ChronopostCodesTransformer;
use LWI\DeliveryTracking\Behavior\ExceptionThrower;
use LWI\DeliveryTracking\DeliveryEvent;
use LWI\DeliveryTracking\DeliveryServiceInterface;
use LWI\DeliveryTracking\DeliveryStatus;
use LWI\DeliveryTracking\Exception\UnsupportedFeatureException;
use \DateTime;

/**
 * Class ChronopostAdapter
 */
class ChronopostAdapter implements DeliveryServiceInterface
{
    use ExceptionThrower, ChronopostCodesTransformer;

    const BASE_URL = 'https://www.chronopost.fr/tracking-cxf/TrackingServiceWS/trackSkybill?'
        .'language=fr_FR'
        .'&skybillNumber=%s'
    ;

    /**
     * @param string $trackingNumber
     *
     * @return DeliveryStatus
     */
    public function getDeliveryStatus($trackingNumber)
    {
        return $this->getLastEvent($trackingNumber)->getStatus();
    }

    /**
     * @param array $trackingNumbers
     *
     * @return array | DeliveryStatus[]
     */
    public function getDeliveryStatuses($trackingNumbers)
    {
        $statuses = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $statuses[$trackingNumber] = $this->getDeliveryStatus($trackingNumber);
        }

        return $statuses;
    }

    /**
     * @param string $trackingNumber
     *
     * @return DeliveryEvent
     */
    public function getLastEvent($trackingNumber)
    {
        $fp = fopen(sprintf(self::BASE_URL, $trackingNumber), 'r');
        $xml = stream_get_contents($fp);
        fclose($fp);
        $xml = new \SimpleXMLElement($xml);

        /* Registering needed namespaces. See http://stackoverflow.com/questions/10322464/ */
        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ns1', 'http://cxf.tracking.soap.chronopost.fr/');

        /** @var null | DeliveryEvent $lastEvent */
        $lastEvent = null;

        $events = $xml->xpath('//soap:Body/ns1:trackSkybillResponse/return/listEvents/events');

        if (empty($events)) {
            $this->throwDataNotFoundException();
        }

        /* XPathing on namespaced XMLs can't be relative */
        foreach ($events as $event) {
            if (isset($event->eventDate) && isset($event->code)) {
                $currentEvent = new DeliveryEvent(
                    $trackingNumber,
                    new DateTime(trim($event->eventDate)),
                    $this->getStateFromCode(trim($event->code))
                );

                if ($lastEvent === null || $lastEvent->getEventDate() < $currentEvent->getEventDate()) {
                    $lastEvent = $currentEvent;
                }
            }
        }

        return $lastEvent;
    }

    /**
     * @param array $trackingNumbers
     *
     * @return array | DeliveryEvent[]
     */
    public function getLastEventForMultipleDeliveries($trackingNumbers)
    {
        $events = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $events[$trackingNumber] = $this->getLastEvent($trackingNumber);
        }

        return $events;
    }

    /**
     * @param string $reference
     *
     * @return void
     * @throws UnsupportedFeatureException
     */
    public function getTrackingNumberByInternalReference($reference)
    {
        $this->throwUnsupportedFeatureException();
    }

    /**
     * @param array $references
     *
     * @return void
     * @throws UnsupportedFeatureException
     */
    public function getTrackingNumbersByInternalReferences($references)
    {
        $this->throwUnsupportedFeatureException();
    }
}
