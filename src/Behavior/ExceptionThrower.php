<?php

namespace LWI\DeliveryTracking\Behavior;

use LWI\DeliveryTracking\Exception\DataNotFoundException;
use LWI\DeliveryTracking\Exception\UnsupportedFeatureException;

/**
 * Class ExceptionThrower
 */
trait ExceptionThrower
{
    /**
     * @throws UnsupportedFeatureException
     */
    public function throwUnsupportedFeatureException()
    {
        throw new UnsupportedFeatureException(__FUNCTION__, (new \ReflectionClass($this))->getShortName());
    }

    /**
     * @throws DataNotFoundException
     */
    public function throwDataNotFoundException()
    {
        throw new DataNotFoundException((new \ReflectionClass($this))->getShortName());
    }
}
