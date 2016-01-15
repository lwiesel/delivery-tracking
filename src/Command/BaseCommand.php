<?php

namespace LWI\DeliveryTracker\Command;

use League\CLImate\CLImate;

/**
 * Class BaseCommand
 */
abstract class BaseCommand
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var array
     */
    protected $argumentsDefinition = [];

    /**
     * @param string $name
     * @return $this
     */
    protected function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    protected function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param array $argumentsDefinition
     * @return $this
     */
    public function setArgumentsDefinition($argumentsDefinition)
    {
        $this->argumentsDefinition = $argumentsDefinition;

        return $this;
    }

    /**
     * @return void
     */
    abstract public function configure();

    /**
     * @param CLImate $cli
     * @param array $arguments
     * @return mixed
     */
    abstract public function execute(CLImate $cli, $arguments);

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getArgumentsDefinition()
    {
        return $this->argumentsDefinition;
    }
}
