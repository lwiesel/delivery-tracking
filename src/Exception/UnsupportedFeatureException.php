<?php

namespace LWI\DeliveryTracking\Exception;

use \Exception;

/**
 * UnsupportedFeatureException
 *
 * Used when a method or a feature is not supported by the delivery service used.
 */
class UnsupportedFeatureException extends Exception
{
    /**
     * UnsupportedFeature constructor.
     * @param string $functionName
     * @param string $adapter
     */
    public function __construct($functionName, $adapter = '')
    {
        parent::__construct(sprintf(
            'The feature "%s" is not supported by this adapter%s.',
            $functionName,
            $adapter ? ' ('.$adapter.')' : ''
        ));
    }
}
