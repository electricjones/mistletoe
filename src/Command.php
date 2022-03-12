<?php namespace ElectricJones\Mistletoe;

/**
 * Class Command
 * @package Mistletoe
 */
class Command
{
    private $command;

    public function __construct($command)
    {

        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }
}
