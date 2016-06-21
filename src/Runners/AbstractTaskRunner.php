<?php
namespace Mistletoe\Runners;
use Mistletoe\Contracts\TaskRunnerInterface;
use Mistletoe\TaskBag;

/**
 * Class AbstractTaskRunner
 * @package Runners
 */
abstract class AbstractTaskRunner implements TaskRunnerInterface
{
    /**
     * @var array of TaskBags
     */
    private $taskBags;

    /**
     * @var string
     */
    private $currentTime = 'now';

    /**
     * @var string
     */
    protected $currentEnvironment;

    /**
     * @var bool
     */
    protected $testing = false;

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
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * @param string|\DateTime $currentTime
     * @return $this
     */
    public function setCurrentTime($currentTime)
    {
        $this->currentTime = $currentTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentEnvironment()
    {
        return $this->currentEnvironment;
    }

    /**
     * @param string $currentEnvironment
     * @return $this
     */
    public function setCurrentEnvironment($currentEnvironment)
    {
        $this->currentEnvironment = $currentEnvironment;
        return $this;
    }

    /**
     * Returns an array of currently due tasks
     * @return array
     * @throws \Exception
     */
    public function getDueTasks()
    {
        $dueTasks = [];
        foreach ($this->taskBags as $taskName => $task) {
            if (!$task instanceof TaskBag) {
                throw new \Exception("Tasks must be instances of TaskBag");
            }

            if ($task->isDue($this->currentTime)) {
                if (in_array($this->currentEnvironment, $task->getEnvironments())) {
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
     */
    public function runAllTasks()
    {
        $tasks = $this->taskBags;
        $success = $this->executeTasks($tasks);

        return $success;
    }

    /**
     * Run the tasks that are due right now
     * @return bool
     * @throws \Exception
     */
    public function runDueTasks()
    {
        $tasks = $this->getDueTasks();
        $success = $this->executeTasks($tasks);

        return $success;
    }

    /**
     * Run a specific task
     * @param $task
     * @return bool
     */
    public function runTask($task)
    {
        $tasks = [
            $task => $this->taskBags[$task]
        ];
        $success = $this->executeTasks($tasks);

        return $success;
    }

    /**
     * Run multiple specific tasks
     * @param array $tasks
     * @return bool
     */
    public function runTasks(array $tasks)
    {
        $list = [];
        foreach ($tasks as $task) {
            $list[$task] = $this->taskBags[$task];
        }

        $success = $this->executeTasks($list);

        return $success;
    }

    public function flagForTesting($switch = false)
    {
        $this->testing = $switch;
        return $this;
    }

    /**
     * @return array
     */
    public function getTaskBags()
    {
        return $this->taskBags;
    }

    /**
     * @param array $taskBags
     * @return $this
     */
    public function setTaskBags($taskBags)
    {
        $this->taskBags = $taskBags;
        return $this;
    }

    /**
     * @param array $tasks
     * @return mixed
     */
    abstract protected function executeTasks(array $tasks);
}
