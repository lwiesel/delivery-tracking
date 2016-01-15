<?php

namespace LWI\DeliveryTracker\Parser;

/**
 * Class FixedWidthColumnsParser
 */
class FixedWidthColumnsParser
{
    /**
     * @var array
     */
    protected $structure = [];

    /**
     * FixedWidthColumnsParser constructor.
     * @param $structure
     */
    public function __construct($structure)
    {
        $this->structure = $structure;
    }

    /**
     * @param $line
     *
     * @return array
     */
    public function parseLine($line)
    {
        $result = [];
        $cursor = 0;

        foreach ($this->structure as $field => $width) {
            $fieldValue = trim(mb_substr($line, $cursor, $width));

            if ($fieldValue !== '') {
                $result[$field] = $fieldValue;
            }

            $cursor += $width;
        }

        return $result;
    }

    /**
     * @param $content
     *
     * @return array
     */
    public function parseMultiLine($content)
    {
        $lines = explode("\n", $content);
        $results = [];

        foreach ($lines as $line) {
            $lineData = $this->parseLine($line);

            if (!empty($lineData)) {
                $results[] = $lineData;
            }
        }

        return $results;
    }
}
