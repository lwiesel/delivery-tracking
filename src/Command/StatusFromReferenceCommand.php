<?php

namespace LWI\DeliveryTracker\Command;

use LWI\DeliveryTracker\Adapter\ChronopostFtpAdapter;
use LWI\DeliveryTracker\DeliveryTracking;
use League\CLImate\CLImate;

/**
 * Class StatusFromReferenceCommand
 */
class StatusFromReferenceCommand extends BaseCommand
{
    /**
     *  Command configuration
     */
    public function configure()
    {
        $this
            ->setName('statusFromRef')
            ->setDescription('Get delivery status from internal reference')
            ->setArgumentsDefinition([
                'reference' => [
                    'description'   => 'The internal delivery reference.',
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
        if (!$arguments['reference']) {
            $cli->error('ERROR. No reference provided.');
            return null;
        }

        $cli->comment('Warning: this command may be slow. It may download and parse a lot of file.');
        $cli->comment(
            'I you know the tracking number, you may want to use the `tracking:status [trackingNumber]` command'
        );

        $chronopostAdapter = new ChronopostFtpAdapter([
            'host' => 'ftpserv.chronopost.fr',
            'username' => 'arconseil',
            'password' => '!arcnsl$',
        ]);
        $deliveryTracking = new DeliveryTracking($chronopostAdapter);

        $cli->br();
        try {
            $status = $deliveryTracking->getDeliveryStatusByInternalReference($arguments['reference']);

            $cli->out(sprintf(
                'Delivery #%s is <green>%s</green>',
                $arguments['reference'],
                $status
            ));

            $trackingNumber = $deliveryTracking->getTrackingNumberByInternalReference($arguments['reference']);

            $cli->whisper(sprintf('The associated tracking Number is %s.', $trackingNumber));
        } catch (\Exception $e) {
            $cli->error(sprintf('ERROR. %s', $e->getMessage()));
        }
        $cli->br();
    }
}
