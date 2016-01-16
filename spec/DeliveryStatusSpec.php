<?php

namespace spec\LWI\DeliveryTracking;

use LWI\DeliveryTracking\DeliveryStatus;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DeliveryStatusSpec extends ObjectBehavior
{
    protected $states = ['InProgress', 'Delivered'];

    function let()
    {
        $this->beConstructedThrough('stateDelivered');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('LWI\DeliveryTracking\DeliveryStatus');
    }

    function it_should_have_a_state()
    {
        $this->isDelivered()->shouldEqual(true);
    }

    function it_should_be_exported_as_string()
    {
        $this->__toString()->shouldBeString();
        $this->__toString()->shouldEqual(DeliveryStatus::STATE_DELIVERED);
    }

    function it_should_have_a_delivered_status()
    {
        $this->check_it_has_only_the_proper_status('Delivered');
    }

    function it_should_have_a_in_progress_status()
    {
        $this->check_it_has_only_the_proper_status('InProgress');
    }

    protected function check_it_has_only_the_proper_status($status)
    {
        $this->beConstructedThrough('state'.$status);
        $this->isDelivered()->shouldEqual($status == 'Delivered');
        $this->isInProgress()->shouldEqual($status == 'InProgress');
    }
}
