<?php namespace ElectricJones\Mistletoe\Contracts;


/**
 * Class TaskRunner
 * @package Mistletoe
 */
interface TaskRunnerInterface
{
    /**
     * @return mixed
     */
    public function getCurrentTime();

    /**
     * @param string|\DateTime $currentTime
     * @return $this
     */
    public function setCurrentTime($currentTime);

    /**
     * @return string
     */
    public function getCurrentEnvironment();

    /**
     * @param string $currentEnvironment
     * @return $this
     */
    public function setCurrentEnvironment($currentEnvironment);

    /**
     * Returns an array of currently due tasks
     * @return array
     * @throws \Exception
     */
    public function getDueTasks();

    /**
     * Force run every registered task
     * @return bool
     */
    public function runAllTasks();

    /**
     * Run the tasks that are due right now
     * @return bool
     * @throws \Exception
     */
    public function runDueTasks();

    /**
     * Run a specific task
     * @param $task
     * @return bool
     */
    public function runTask($task);

    /**
     * Run multiple specific tasks
     * @param array $tasks
     * @return bool
     */
    public function runTasks(array $tasks);

    /**
     * In testing mode, we return the executed command strings, instead of executing them
     * @param bool $switch
     * @return mixed
     */
    public function flagForTesting($switch = false);
}
