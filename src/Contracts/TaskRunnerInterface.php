<?php namespace ElectricJones\Mistletoe\Contracts;


use DateTime;
use Exception;

/**
 * Class TaskRunner
 * @package Mistletoe
 */
interface TaskRunnerInterface
{
    /**
     * @return mixed
     */
    public function getCurrentTime(): mixed;

    /**
     * @param DateTime|string $currentTime
     * @return $this
     */
    public function setCurrentTime(DateTime|string $currentTime): static;

    /**
     * @return string
     */
    public function getCurrentEnvironment(): string;

    /**
     * @param string $currentEnvironment
     * @return $this
     */
    public function setCurrentEnvironment(string $currentEnvironment): static;

    /**
     * Returns an array of currently due tasks
     * @return array
     * @throws Exception
     */
    public function getDueTasks(): array;

    /**
     * Force run every registered task
     * @return bool
     */
    public function runAllTasks(): bool;

    /**
     * Run the tasks that are due right now
     * @return bool
     * @throws Exception
     */
    public function runDueTasks(): bool;

    /**
     * Run a specific task
     * @param $task
     * @return bool
     */
    public function runTask($task): bool;

    /**
     * Run multiple specific tasks
     * @param array $tasks
     * @return bool
     */
    public function runTasks(array $tasks): bool;

    /**
     * In testing mode, we return the executed command strings, instead of executing them
     * @param bool $switch
     * @return mixed
     */
    public function flagForTesting(bool $switch = false): mixed;
}
