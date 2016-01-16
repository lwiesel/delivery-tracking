<?php

namespace LWI\DeliveryTracking\Command;

use LWI\DeliveryTracking\Adapter\ChronopostAdapter;
use LWI\DeliveryTracking\DeliveryTracking;
use League\CLImate\CLImate;

/**
 * Class StatusCommand
 */
class StatusCommand extends BaseCommand
{
    /**
     *  Command configuration
     */
    public function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Get delivery status from the tracking number')
            ->setArgumentsDefinition([
                'trackingNumber' => [
                    'description'   => 'The tracking number provided by the delivery service.',
                ],
            ])
        ;
    }

    /**
     * @param CLImate $cli
     * @param array $arguments
     * @return void
     */
    public function execute(CLImate $cli, $arguments)
    {
        if (!$arguments['trackingNumber']) {
            $cli->error('ERROR. No tracking number provided.');
            return null;
        }

        $chronopostAdapter = new ChronopostAdapter();
        $deliveryTracking = new DeliveryTracking($chronopostAdapter);

        $cli->br();
        try {
            $status = $deliveryTracking->getDeliveryStatus($arguments['trackingNumber']);

            $cli->out(sprintf(
                'Delivery #%s is <green>%s</green>',
                $arguments['trackingNumber'],
                $status
            ));
        } catch (\Exception $e) {
            $cli->error(sprintf('ERROR. %s', $e->getMessage()));
        }
        $cli->br();
    }
}
