<?php

namespace spec\LWI\DeliveryTracking\Parser;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FixedWidthColumnsParserSpec extends ObjectBehavior
{
    protected $fileStructure = [
        'columnA' => 4,
        'columnB' => 3,
    ];

    protected $oneLine = ' AA BB ';
    protected $anotherLine = 'AAAABBB';

    function let()
    {
        $this->beConstructedWith($this->fileStructure);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('LWI\DeliveryTracking\Parser\FixedWidthColumnsParser');
    }

    function it_should_parse_one_line()
    {
        $this->parseLine($this->oneLine)->shouldBeArray();
        $this->parseLine($this->oneLine)->shouldHaveCount(2);
        $this->parseLine($this->oneLine)->shouldContain('AA');
        $this->parseLine($this->oneLine)->shouldContain('BB');
    }

    function it_should_parse_several_lines()
    {
        $multiLine = $this->oneLine."\n".$this->anotherLine;

        $this->parseMultiLine($multiLine)->shouldBeArray();
        $this->parseMultiLine($multiLine)->shouldHaveCount(2);
        $this->parseMultiLine($multiLine)->shouldContain($this->parseLine($this->oneLine));
        $this->parseMultiLine($multiLine)->shouldContain($this->parseLine($this->anotherLine));
    }
}
