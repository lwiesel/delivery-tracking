<?php

namespace spec\LWI\DeliveryTracking;

use LWI\DeliveryTracking\DeliveryEvent;
use LWI\DeliveryTracking\DeliveryServiceInterface;
use LWI\DeliveryTracking\DeliveryStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeliveryTrackingSpec extends ObjectBehavior
{
    function let(
        DeliveryServiceInterface $deliveryService,
        DeliveryStatus $deliveryStatus,
        DeliveryEvent $deliveryEvent
    ) {
        $deliveryService->getDeliveryStatus('trackingNumber')
            ->willReturn($deliveryStatus);
        $deliveryService->getTrackingNumberByInternalReference('internalReference')
            ->willReturn('trackingNumber');
        $deliveryService->getLastEvent('trackingNumber')
            ->willReturn($deliveryEvent);

        $this->beConstructedWith($deliveryService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('LWI\DeliveryTracking\DeliveryTracking');
    }

    function it_should_retrieve_a_delivery_status_from_a_tracking_number()
    {
        $this->getDeliveryStatus('trackingNumber')
            ->shouldBeAnInstanceOf('LWI\DeliveryTracking\DeliveryStatus');
    }

    function it_should_retrieve_a_delivery_status_from_an_internal_reference()
    {
        $this->getDeliveryStatusByInternalReference('internalReference')
            ->shouldBeAnInstanceOf('LWI\DeliveryTracking\DeliveryStatus');
    }

    function it_should_retrieve_the_last_delivery_event_from_a_tracking_number()
    {
        $this->getLastEvent('trackingNumber')
            ->shouldBeAnInstanceOf('LWI\DeliveryTracking\DeliveryEvent');
    }

    function it_should_retrieve_a_tracking_number_from_an_internal_reference()
    {
        $this->getTrackingNumberByInternalReference('internalReference')
            ->shouldEqual('trackingNumber');
    }
}
