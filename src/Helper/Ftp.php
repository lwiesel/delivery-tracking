<?php

namespace LWI\DeliveryTracker\Helper;

class Ftp
{
    public function __construct($url)
    {
        if (!extension_loaded('ftp')) {
            throw new \Exception('FTP extesion not loaded.');
        }
    }
}
