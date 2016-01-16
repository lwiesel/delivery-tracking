<?php

namespace spec\LWI\DeliveryTracking;

use LWI\DeliveryTracking\DeliveryStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeliveryEventSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('LWI\DeliveryTracking\DeliveryEvent');
    }

    function let(\DateTime $eventDate, DeliveryStatus $deliveryStatus)
    {
        $this->beConstructedWith('trackingNumber', $eventDate, $deliveryStatus);
    }

    function it_should_have_a_tracking_number()
    {
        $this->getTrackingNumber()->shouldEqual('trackingNumber');
    }

    function it_should_have_an_event_date()
    {
        $this->getEventDate()->shouldHaveType('\DateTime');
    }

    function it_should_have_a_status()
    {
        $this->getStatus()->shouldHaveType('LWI\DeliveryTracking\DeliveryStatus');
    }

    function it_should_be_transformed_to_an_array(\DateTime $eventDate, DeliveryStatus $deliveryStatus)
    {
        $deliveryStatus->__toString()->willReturn('status');

        $this->beConstructedWith('trackingNumber', $eventDate, $deliveryStatus);

        $this->toArray()->shouldBeArray();

        $this->toArray()->shouldHaveKey('trackingNumber');
        $this->toArray()->shouldHaveKey('internalNumber');
        $this->toArray()->shouldHaveKey('eventDate');
        $this->toArray()->shouldHaveKey('status');

        $this->toArray()['trackingNumber']->shouldBeString('trackingNumber');
        $this->toArray()['trackingNumber']->shouldEqual('trackingNumber');

        $this->toArray()['internalNumber']->shouldEqual(null);

        $this->toArray()['eventDate']->shouldHaveType('\DateTime');
        $this->toArray()['eventDate']->shouldEqual($eventDate);

        $this->toArray()['status']->shouldBeString();
        $this->toArray()['status']->shouldEqual('status');
    }

    function it_should_be_transformed_to_an_array_with_internal_number(
        \DateTime $eventDate,
        DeliveryStatus $deliveryStatus
    ) {
        $deliveryStatus->__toString()->willReturn('status');

        $this->beConstructedWith('trackingNumber', $eventDate, $deliveryStatus, 'internalNumber');

        $this->toArray()->shouldBeArray();

        $this->toArray()->shouldHaveKey('trackingNumber');
        $this->toArray()->shouldHaveKey('internalNumber');
        $this->toArray()->shouldHaveKey('eventDate');
        $this->toArray()->shouldHaveKey('status');

        $this->toArray()['trackingNumber']->shouldBeString('trackingNumber');
        $this->toArray()['trackingNumber']->shouldEqual('trackingNumber');

        $this->toArray()['internalNumber']->shouldBeString();
        $this->toArray()['internalNumber']->shouldEqual('internalNumber');

        $this->toArray()['eventDate']->shouldHaveType('\DateTime');
        $this->toArray()['eventDate']->shouldEqual($eventDate);

        $this->toArray()['status']->shouldBeString();
        $this->toArray()['status']->shouldEqual('status');
    }
}
