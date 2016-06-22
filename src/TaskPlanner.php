<?php
namespace Mistletoe;
use Mistletoe\Contracts\TaskRunnerInterface;
use Mistletoe\Runners\GenericTaskRunner;

/**
 * Class TaskPlanner
 * @package FBS\Planner
 */
class TaskPlanner
{
    /** @var array Tasks currently scheduled */
    protected $tasks = [];

    /** @var  TaskBag */
    protected $currentTask;

    /** @var  string Namespace prefix */
    protected $nsPrefix = '';

    protected $currentEnvironment = self::PRODUCTION_ENVIRONMENT;

    /* Environment Constants */
    const PRODUCTION_ENVIRONMENT = 'PRODUCTION';
    const DEVELOPMENT_ENVIRONMENT = 'DEVELOPMENT';

    /**
     * @var \Mistletoe\Contracts\TaskRunnerInterface
     */
    protected $taskRunner;

    /**
     * @var integer
     */
    protected $closureIncrement = 0;

    /**
     * @var string
     */
    protected $currentTime;

    /**
     * @var bool
     */
    protected $testing = false;

    /**
     * Begin a new Task Chain
     * @param string|\Closure $task
     * @return $this
     */
    public function add($task)
    {
        $body = null;
        if ($task instanceof \Closure || $task instanceof Command) {
            $body = $task;
            $task = $this->getNextClosureIncrement();
        }

        $this->createNewTask($task, $body);
        return $this;
    }

    /* Schedule a full expression */
    /**
     * Add a full expression
     * @param string $expression
     * @return $this
     */
    public function schedule($expression)
    {
        $this->getCurrentTask()->setCronExpression($expression);
        return $this;
    }

    public function always()
    {
        return $this->schedule('* * * * *');
    }

    /* Intervals */
    /**
     * @return $this
     */
    public function yearly()
    {
        $this->getCurrentTask()->setInterval('@yearly');
        return $this;
    }

    /**
     * @return TaskPlanner
     */
    public function annually()
    {
        return $this->yearly();
    }

    /**
     * @return $this
     */
    public function monthly()
    {
        $this->getCurrentTask()->setInterval('@monthly');
        return $this;
    }

    /**
     * @return $this
     */
    public function weekly()
    {
        $this->getCurrentTask()->setInterval('@weekly');
        return $this;
    }

    /**
     * @return $this
     */
    public function daily()
    {
        $this->getCurrentTask()->setInterval('@daily');
        return $this;
    }

    /**
     * @return $this
     */
    public function hourly()
    {
        $this->getCurrentTask()->setInterval('@hourly');
        return $this;
    }

    /* Time intervals */

    public function everyXHours($hrs)
    {
        $this->getCurrentTask()->setHour("*/{$hrs}");
        return $this;
    }

    public function everyXMinutes($mins)
    {
        $this->getCurrentTask()->setMinute("*/{$mins}");
        return $this;
    }

    /* @todo: this could be cleaned up and expanded for minutes, hours, days, and weeks
     * @param $name
     * @param $arguments
     * @return
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'every') === false) {
            throw new \BadMethodCallException("{$name} is not a valid method. Did you mean everyXHours? or everyXMinutes()?");
        }

        // Get Increment
        $increment = $name;
        $increment = str_replace('every', '', $increment);
        $increment = str_replace('Minutes', '', $increment);
        $increment = str_replace('Hours', '', $increment);
        $increment = (int) $increment;

        // Get Hours or Minutes
        $method = $name;
        if (strpos($method, 'Minutes')){
            $method = 'Minutes';
        }

        if (strpos($method, 'Hours')) {
            $method = 'Hours';
        }

        // Build Method
        $method = "everyX{$method}";

        // Execute
        return $this->$method($increment);
    }

    /* Times */
    /**
     * @param string $time
     * @return $this
     */
    public function at($time)
    {
        $this->getCurrentTask()->addTime($time);
        return $this;
    }

    /**
     * @return TaskPlanner
     */
    public function atMidnight()
    {
        return $this->at('24:00');
    }

    /**
     * @return TaskPlanner
     */
    public function atNoon()
    {
        return $this->at('12:00');
    }

    /**
     * @param string|array|integer $minute
     * @return $this
     */
    public function atMinute($minute)
    {
        $this->getCurrentTask()->addMinute($minute);
        return $this;
    }

    /**
     * @param string|array|integer $minute
     * @return TaskPlanner
     */
    public function andAtMinute($minute)
    {
        return $this->atMinute($minute);
    }

    /**
     * @param string|array|integer $hour
     * @return $this
     */
    public function atHour($hour)
    {
        $this->getCurrentTask()->addHour($hour);
        return $this;
    }

    /**
     * @param string|array|integer $hour
     * @return TaskPlanner
     */
    public function andAtHour($hour)
    {
        return $this->atHour($hour);
    }

    /**
     * @param string|array|integer $date
     * @return $this
     */
    public function on($date)
    {
        $this->getCurrentTask()->addDate($date);
        return $this;
    }

    /**
     * @param string|array|integer $date
     * @return TaskPlanner
     */
    public function andOn($date)
    {
        return $this->on($date);
    }

    /**
     * @param string|array|integer $day
     * @return $this
     */
    public function onDay($day)
    {
        $this->getCurrentTask()->addDay($day);
        return $this;
    }

    /**
     * @param string|array|integer $day
     * @return TaskPlanner
     */
    public function andOnDay($day)
    {
        return $this->onDay($day);
    }

    /**
     * @param string|array|integer $month
     * @return $this
     */
    public function onMonth($month)
    {
        $this->getCurrentTask()->addMonth($month);
        return $this;
    }

    /**
     * @param string|array|integer $month
     * @return TaskPlanner
     */
    public function andOnMonth($month)
    {
        return $this->onMonth($month);
    }

    /**
     * @param string|array|integer $weekday
     * @return $this
     */
    public function onWeekday($weekday)
    {
        $this->getCurrentTask()->addWeekday($weekday);
        return $this;
    }

    /**
     * @param string|array|integer $weekday
     * @return TaskPlanner
     */
    public function andOnWeekday($weekday)
    {
        return $this->onWeekday($weekday);
    }

    /**
     * @return $this
     */
    public function onSunday()
    {
        return $this->onWeekday(0);
    }

    /**
     * @return TaskPlanner
     */
    public function onMonday()
    {
        return $this->onWeekday(1);
    }

    /**
     * @return TaskPlanner
     */
    public function onTuesday()
    {
        return $this->onWeekday(2);
    }

    /**
     * @return TaskPlanner
     */
    public function onWednesday()
    {
        return $this->onWeekday(3);
    }

    /**
     * @return TaskPlanner
     */
    public function onThursday()
    {
        return $this->onWeekday(4);
    }

    /**
     * @return TaskPlanner
     */
    public function onFriday()
    {
        return $this->onWeekday(5);
    }

    /**
     * @return TaskPlanner
     */
    public function onSaturday()
    {
        return $this->onWeekday(6);
    }

    /* Environments */
    /**
     * Restrict task to a specific environment
     * @param string $environment
     * @return $this
     */
    public function onEnvironment($environment)
    {
        $this->getCurrentTask()->addEnvironment(strtoupper($environment));
        return $this;
    }

    public function setEnvironments(array $environment)
    {
        array_map(function($value) {
            return strtoupper($value);
        }, $environment);

        $this->getCurrentTask()->setEnvironments($environment);
        return $this;
    }

    /* Alias for onEnvironment() */
    /**
     * @return TaskPlanner
     */
    public function onProductionOnly()
    {
        return $this->setEnvironments([static::PRODUCTION_ENVIRONMENT]);
    }

    /* Alias for onEnvironment() */
    /**
     * @return TaskPlanner
     */
    public function onDevelopmentOnly()
    {
        return $this->setEnvironments([static::DEVELOPMENT_ENVIRONMENT]);
    }

    /**
     * @param string $task
     * @return $this
     */
    public function followedBy($task)
    {
        $this->getCurrentTask()->addFollowedBy($task);
        return $this;
    }

    /* Running Tasks */
    /**
     * @return bool
     */
    public function runDueTasks()
    {
        return $this->getTaskRunner()->runDueTasks();
    }

    /**
     * @return bool
     */
    public function runAllTasks()
    {
        return $this->getTaskRunner()->runAllTasks();
    }

    /**
     * @param $task
     * @return bool
     */
    public function runTask($task)
    {
        return $this->getTaskRunner()->runTask($task);
    }

    /**
     * @param array $tasks
     * @return bool
     */
    public function runTasks(array $tasks)
    {
        return $this->getTaskRunner()->runTasks($tasks);
    }

    /* Getters and Setters */
    /**
     * @param \Mistletoe\Contracts\TaskRunnerInterface $runner
     * @return $this
     */
    public function setTaskRunner(TaskRunnerInterface $runner)
    {
        $this->taskRunner = $runner;
        return $this;
    }

    /**
     * @return TaskRunnerInterface
     */
    public function getTaskRunner()
    {
        $runner = $this->taskRunner ?: (new GenericTaskRunner($this->tasks));
        $runner
            ->setTaskBags($this->tasks)
            ->setCurrentEnvironment(strtoupper($this->currentEnvironment))
            ->setCurrentTime($this->currentTime)
            ->flagForTesting($this->testing);

        return $runner;
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->tasks;
    }

    /**
     * @param string $name
     * @return TaskBag
     */
    public function getTask($name)
    {
        return $this->tasks[$name];
    }

    public function getDueTasks()
    {
        return $this->getTaskRunner()->getDueTasks();
    }

    /**
     * @return string
     */
    public function getNsPrefix()
    {
        return $this->nsPrefix;
    }

    /**
     * @param string $nsPrefix
     * @return $this
     */
    public function setNsPrefix($nsPrefix)
    {
        $this->nsPrefix = $nsPrefix;
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
        $this->currentEnvironment = strtoupper($currentEnvironment);
        return $this;
    }

    /* Internal Methods */
    /**
     * @param string $task
     * @param \Closure|null $body
     */
    protected function createNewTask($task, $body = null)
    {
        $this->tasks[$task] = new TaskBag($task);
        $this->setCurrentTask($task);

        $this->getCurrentTask()->setTask(
            ($body) ? $body : $task
        );
            }

    /**
     * @param string $task
     */
    protected function setCurrentTask($task)
    {
        $this->currentTask = $this->tasks[$task];
    }

    /**
     * @return TaskBag
     */
    protected function getCurrentTask()
    {
        return $this->currentTask;
    }

    /**
     * @return null|string
     */
    private function getNextClosureIncrement()
    {
        $this->closureIncrement++;
        return "_task{$this->closureIncrement}";
    }

    /**
     * @return string
     */
    public function getCurrentTime()
    {
        return $this->currentTime;
    }

    /**
     * @param mixed $currentTime
     * @return $this
     */
    public function setCurrentTime($currentTime)
    {
        $this->currentTime = $currentTime;
        return $this;
    }

    public function flagForTesting()
    {
        $this->testing = true;
    }
}
