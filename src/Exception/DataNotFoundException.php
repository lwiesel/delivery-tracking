<?php

namespace LWI\DeliveryTracking\Exception;

use \Exception;

/**
 * DataNotFoundException
 *
 * Used when the requested data for a delivery os not found.
 */
class DataNotFoundException extends Exception
{
    /**
     * DataNotFoundException constructor.
     * @param string $adapter
     */
    public function __construct($adapter = '')
    {
        parent::__construct(sprintf(
            'The requested data has not been found by this adapter%s.',
            $adapter ? ' ('.$adapter.')' : ''
        ));
    }
}
