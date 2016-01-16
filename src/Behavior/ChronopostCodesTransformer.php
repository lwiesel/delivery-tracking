<?php

namespace LWI\DeliveryTracking\Behavior;

use LWI\DeliveryTracking\DeliveryStatus;

trait ChronopostCodesTransformer
{
    /**
     * @param string $code
     *
     * @return null | DeliveryStatus
     */
    protected function getStateFromCode($code)
    {
        $state = null;

        switch ($code) {
            case 'D':
            case 'D1':
            case 'D2':
                $state = DeliveryStatus::stateDelivered();
                break;

            default:
                $state = DeliveryStatus::stateInProgress();
                break;
        }

        return $state;
    }
}
