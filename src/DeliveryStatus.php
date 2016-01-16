<?php

namespace LWI\DeliveryTracking;

/**
 * Class DeliveryStatus
 */
class DeliveryStatus
{
    const STATE_DELIVERED = 'delivered';

    const STATE_IN_PROGRESS = 'in_progress';

    /**
     * Current preparation state
     *
     * @var string
     */
    protected $state;

    /**
     * ExpeditionState constructor.
     * @param $state
     */
    protected function __construct($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->state;
    }

    /**
     * @return DeliveryStatus
     */
    public static function stateDelivered()
    {
        return new self(self::STATE_DELIVERED);
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return $this->state == self::STATE_DELIVERED;
    }

    /**
     * @return DeliveryStatus
     */
    public static function stateInProgress()
    {
        return new self(self::STATE_IN_PROGRESS);
    }

    /**
     * @return bool
     */
    public function isInProgress()
    {
        return $this->state == self::STATE_IN_PROGRESS;
    }
}
