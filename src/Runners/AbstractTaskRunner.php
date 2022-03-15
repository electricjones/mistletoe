<?php
namespace ElectricJones\Mistletoe\Runners;

use DateTime;
use ElectricJones\Mistletoe\Contracts\TaskRunnerInterface;
use ElectricJones\Mistletoe\Task;
use Exception;

/**
 * Class AbstractTaskRunner
 * @package Runners
 */
abstract class AbstractTaskRunner implements TaskRunnerInterface
{
    /**
     * @var array of TaskBags
     */
    private array $taskBags;

    /**
     * @var string
     */
    private string $currentTime = 'now';

    /**
     * @var string
     */
    protected string $currentEnvironment;

    /**
     * @var bool
     */
    protected bool $testing = false;

    /**
     * TaskRunner constructor.
     * @param array $taskBags
     */
    public function __construct(array $taskBags = [])
    {
        $this->taskBags = $taskBags;
    }

    /**
     * @return mixed
     */
    public function getCurrentTime(): static
    {
        return $this->currentTime;
    }

    /**
     * @param DateTime|string $currentTime
     * @return $this
     * @throws Exception
     */
    public function setCurrentTime(DateTime|string $currentTime): static
    {
        //if (!is_string($currentTime)) {
        //    throw new \Exception("Current time must be a string");
        //}

        $this->currentTime = $currentTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentEnvironment(): string
    {
        return $this->currentEnvironment;
    }

    /**
     * @param string $currentEnvironment
     * @return $this
     */
    public function setCurrentEnvironment(string $currentEnvironment): static
    {
        $this->currentEnvironment = $currentEnvironment;
        return $this;
    }

    /**
     * Returns an array of currently due tasks
     * @return array
     * @throws Exception
     */
    public function getDueTasks(): array
    {
        $dueTasks = [];
        foreach ($this->taskBags as $taskName => $task) {
            if (!$task instanceof Task) {
                throw new Exception("Tasks must be instances of TaskBag");
            }

            if ($task->isDue($this->currentTime)) {
                if ($task->getEnvironments() === [] or in_array($this->currentEnvironment, $task->getEnvironments())) {
                    $dueTasks[$taskName] = $task;
                }
            }
        }

        return $dueTasks;
    }

    /* Run Tasks */
    /**
     * Force run every registered task
     * @return bool
     * @todo: return a more appropriate status object
     */
    public function runAllTasks(): bool
    {
        $tasks = $this->taskBags;
        return $this->executeTasks($tasks);
    }

    /**
     * Run the tasks that are due right now
     * @return bool
     * @throws Exception
     * @todo: return a more appropriate status object
     */
    public function runDueTasks(): bool
    {
        $tasks = $this->getDueTasks();
        return $this->executeTasks($tasks);
    }

    /**
     * Run a specific task
     * @param $task
     * @todo: return a more appropriate status object
     * @return bool
     */
    public function runTask($task): bool
    {
        $tasks = [
            $task => $this->taskBags[$task]
        ];
        return $this->executeTasks($tasks);
    }

    /**
     * Run multiple specific tasks
     * @param array $tasks
     * @todo: return a more appropriate status object
     * @return bool
     */
    public function runTasks(array $tasks): bool
    {
        $list = [];
        foreach ($tasks as $task) {
            $list[$task] = $this->taskBags[$task];
        }

        return $this->executeTasks($list);
    }

    /**
     * @param bool $switch
     * @return $this
     */
    public function flagForTesting(bool $switch = false): static
    {
        $this->testing = $switch;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaskBags(): array
    {
        return $this->taskBags;
    }

    /**
     * @param array $taskBags
     * @return $this
     */
    public function setTaskBags($taskBags): static
    {
        $this->taskBags = $taskBags;
        return $this;
    }

    /**
     * @param array $tasks
     * @return mixed
     */
    abstract protected function executeTasks(array $tasks): mixed;
}
