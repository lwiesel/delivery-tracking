<?php

namespace spec\LWI\DeliveryTracking\Adapter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ChronopostAdapterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('LWI\DeliveryTracking\Adapter\ChronopostAdapter');
    }
}
