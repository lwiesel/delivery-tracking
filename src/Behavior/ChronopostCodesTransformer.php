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
        switch ($code) {
            case 'D':
            case 'D1':
            case 'D2':
            case 'RG':
            case 'DD':
            case 'B':
            case 'U':
            case 'VC':
            case 'RI':
            case 'RR':
                $state = DeliveryStatus::stateDelivered();
                break;

            default:
                $state = DeliveryStatus::stateInProgress();
                break;
        }

        return $state;
    }
}
