<?php

namespace LWI\DeliveryTracker\Adapter;

use LWI\DeliveryTracker\Behavior\ChronopostCodesTransformer;
use LWI\DeliveryTracker\Behavior\ExceptionThrower;
use LWI\DeliveryTracker\DeliveryEvent;
use LWI\DeliveryTracker\DeliveryServiceInterface;
use LWI\DeliveryTracker\DeliveryStatus;
use LWI\DeliveryTracker\Exception\DataNotFoundException;
use LWI\DeliveryTracker\Parser\FixedWidthColumnsParser;
use \DateTime;
use \DateTimeZone;

/**
 * Class ChronopostFtpAdapter
 */
class ChronopostFtpAdapter extends AbstractFtpAdapter implements DeliveryServiceInterface
{
    use ExceptionThrower, ChronopostCodesTransformer;

    /**
     * @var array
     */
    protected $fileStructure = [
        'accountNumber' => 8,
        'subAccountNumber' => 3,
        'date' => 8,
        'expeditionRef' => 35,
        'target' => 35,
        'chronopostNumber' => 13,
        'chronopostBarcode' => 45,
        'status' => 3,
        'statusReason' => 3,
        'statusDate' => 8,
        'statusTime' => 4,
        'zipcode' => 9,
        'receiver' => 35,
    ];

    /**
     * File pattern to retrieve, will be validated by preg_match
     *
     * @var string
     */
    protected $filePattern = '/CHRARCONSEIL_EDP02_40478001_/';

    /**
     * @var FixedWidthColumnsParser
     */
    protected $fixedWidthParser;


    /**
     * Paths of the already downloaded files
     *
     * @var array
     */
    protected $downloadedFiles = [];

    /**
     * Parsed events locally and temporary stored
     *
     * @var array | DeliveryEvent[]
     */
    protected $events = [];

    /**
     * Parsed events locally and temporary stored, indexed by internal number
     *
     * @var array | DeliveryEvent[]
     */
    protected $eventsByInternalNumber = [];

    /**
     * Number of file parsed while searching for a delivery before it throws a DataNotFoundException
     *
     * @var int
     */
    protected $depth = 7 * 24;

    /**
     * ChronopostFtpAdapter constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->filePattern = isset($config['filePattern']) ? $config['filePattern'] : $this->filePattern;

        $this->fixedWidthParser = new FixedWidthColumnsParser($this->fileStructure);

        parent::__construct($config);
    }

    /**
     * @param string $trackingNumber
     *
     * @return DeliveryStatus
     */
    public function getDeliveryStatus($trackingNumber)
    {
        return $this->getLastEvent($trackingNumber)->getStatus();
    }

    /**
     * @param array $trackingNumbers
     *
     * @return array | DeliveryStatus[]
     */
    public function getDeliveryStatuses($trackingNumbers)
    {
        $statuses = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $statuses[$trackingNumber] = $this->getDeliveryStatus($trackingNumber);
        }

        return $statuses;
    }


    /**
     * @param $trackingNumber
     *
     * @return DeliveryEvent
     *
     * @throws DataNotFoundException
     */
    public function getLastEvent($trackingNumber)
    {
        $try = 0;
        while ($try < $this->depth && !isset($this->events[$trackingNumber])) {
            $this->retrieveOneMoreFile();
            $try ++;
        }

        if ($try >= $this->depth && !isset($this->events[$trackingNumber])) {
            $this->throwDataNotFoundException();
        }

        return $this->events[$trackingNumber];
    }

    /**
     * @param array $trackingNumbers
     *
     * @return array | DeliveryEvent[]
     */
    public function getLastEventForMultipleDeliveries($trackingNumbers)
    {
        $events = [];

        foreach ($trackingNumbers as $trackingNumber) {
            $events[$trackingNumber] = $this->getLastEvent($trackingNumber);
        }

        return $events;
    }

    /**
     * @param string $reference
     *
     * @return string
     */
    public function getTrackingNumberByInternalReference($reference)
    {
        $try = 0;
        while ($try < $this->depth && !isset($this->eventsByInternalNumber[$reference])) {
            $this->retrieveOneMoreFile();
            $try ++;
        }

        if ($try >= $this->depth && !isset($this->eventsByInternalNumber[$reference])) {
            $this->throwDataNotFoundException();
        }

        return $this->eventsByInternalNumber[$reference]->getTrackingNumber();
    }

    /**
     * @param array $references
     *
     * @return void
     */
    public function getTrackingNumbersByInternalReferences($references)
    {
        // TODO
    }


    /**
     *  Retrieve one more file and save the events it in the local store if the are more recent
     */
    protected function retrieveOneMoreFile()
    {
        $lastUnreadFilePath = $this->getLastUnreadFilePath();

        $events = $this->retrieveFileEvents($lastUnreadFilePath);

        foreach ($events as $event) {
            if (isset($this->events[$event->getTrackingNumber()])) {
                if ($event->getEventDate() > $this->events[$event->getTrackingNumber()]->getEventDate()) {
                    $this->events[$event->getTrackingNumber()] = $event;
                }
            } else {
                $this->events[$event->getTrackingNumber()] = $event;

                if ($event->getInternalNumber()) {
                    $this->eventsByInternalNumber[$event->getInternalNumber()] = $event;
                }
            }
        }
    }

    /**
     * Retrieve the path of the more recent unread file
     * so if a new file is remotely created before this method is called, its path will be returned
     *
     * @return string
     */
    protected function getLastUnreadFilePath()
    {
        $lastUnreadFile = null;
        $chronopostFiles = $this->listDirectoryContents('OUT');

        $filteredFiles = [];
        foreach ($chronopostFiles as $file) {
            if (preg_match($this->filePattern, $file['path'])) {
                $filteredFiles[] = $file;
            }
        }
        $chronopostFiles = $filteredFiles;

        usort($chronopostFiles, function ($fileA, $fileB) {
            return ($fileA['date'] > $fileB['date']) ? -1 : 1;
        });

        foreach ($chronopostFiles as $file) {
            if (!in_array($file['path'], $this->downloadedFiles)) {
                $lastUnreadFile = $file['path'];
                break;
            }
        }

        if ($lastUnreadFile == null) {
            $this->throwDataNotFoundException();
        }

        return $lastUnreadFile;
    }

    /**
     * @param $path
     *
     * @return array | DeliveryEvent[]
     */
    protected function retrieveFileEvents($path)
    {
        $lastFileContent = $this->read($path)['contents'];

        $eventsRawData = $this->fixedWidthParser->parseMultiLine($lastFileContent);

        $events = [];
        foreach ($eventsRawData as $singleEventRawData) {
            $eventDate = DateTime::createFromFormat(
                'Ymd-Hi',
                $singleEventRawData['statusDate'].'-'.$singleEventRawData['statusTime'],
                new DateTimeZone('Europe/Paris')
            );

            $eventCode = isset($singleEventRawData['status']) ? $singleEventRawData['status'] : '';
            $internalNumber = !empty($singleEventRawData['expeditionRef']) ? $singleEventRawData['expeditionRef'] : '';

            $events[] = new DeliveryEvent(
                $singleEventRawData['chronopostNumber'],
                $eventDate,
                $this->getStateFromCode($eventCode),
                $internalNumber
            );
        }

        $this->downloadedFiles[] = $path;

        return $events;
    }
}
