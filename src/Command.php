<?php namespace ElectricJones\Mistletoe;

/**
 * Class Command
 * @package Mistletoe
 */
class Command
{
    private mixed $command;

    public function __construct($command)
    {

        $this->command = $command;
    }

    /**
     * @return mixed
     */
    public function getCommand(): mixed
    {
        return $this->command;
    }

    /**
     * @param mixed $command
     */
    public function setCommand(mixed $command)
    {
        $this->command = $command;
    }
}
