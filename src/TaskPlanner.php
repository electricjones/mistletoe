<?php
namespace ElectricJones\Mistletoe;

use Closure;
use ElectricJones\Mistletoe\Contracts\TaskRunnerInterface;
use ElectricJones\Mistletoe\Runners\GenericTaskRunner;
use Exception;

/**
 * Class TaskPlanner
 * @package FBS\Planner
 */
class TaskPlanner
{
    /** @var array Tasks currently scheduled */
    protected array $tasks = [];

    /** @var  Task */
    protected Task $currentTask;

    /** @var  string Namespace prefix */
    protected string $nsPrefix = '';

    protected string $currentEnvironment = self::PRODUCTION_ENVIRONMENT;

    /* Environment Constants */
    const PRODUCTION_ENVIRONMENT = 'PRODUCTION';
    const DEVELOPMENT_ENVIRONMENT = 'DEVELOPMENT';

    /**
     * @var TaskRunnerInterface
     */
    protected TaskRunnerInterface $taskRunner;

    /**
     * @var integer
     */
    protected int $closureIncrement = 0;

    /**
     * @var string
     */
    protected string $currentTime;

    /**
     * @var bool
     */
    protected bool $testing = false;


    /**
     * Begin a new Task Chain
     * @param Closure|string $task
     * @return $this
     */
    public function add(Closure|string|Command $task): static
    {
        $body = null;
        if ($task instanceof Closure || $task instanceof Command) {
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
    public function schedule(string $expression): static
    {
        $this->getCurrentTask()->setCronExpression($expression);
        return $this;
    }

    public function always(): static
    {
        return $this->schedule('* * * * *');
    }

    /* Intervals */
    /**
     * @return $this
     */
    public function yearly(): static
    {
        $this->getCurrentTask()->setInterval('@yearly');
        return $this;
    }

    /**
     * @return TaskPlanner
     */
    public function annually(): static
    {
        return $this->yearly();
    }

    /**
     * @return $this
     */
    public function monthly(): static
    {
        $this->getCurrentTask()->setInterval('@monthly');
        return $this;
    }

    /**
     * @return $this
     */
    public function weekly(): static
    {
        $this->getCurrentTask()->setInterval('@weekly');
        return $this;
    }

    /**
     * @return $this
     */
    public function daily(): static
    {
        $this->getCurrentTask()->setInterval('@daily');
        return $this;
    }

    /**
     * @return $this
     */
    public function hourly(): static
    {
        $this->getCurrentTask()->setInterval('@hourly');
        return $this;
    }

    /* Time intervals */

    public function everyXHours($hrs): static
    {
        $this->getCurrentTask()->setHours("*/{$hrs}");
        return $this;
    }

    public function everyXMinutes($mins): static
    {
        $this->getCurrentTask()->setMinutes("*/{$mins}");
        return $this;
    }

    /* @param $name
     * @param $arguments
     * @return mixed
     * @todo: this could be cleaned up and expanded for minutes, hours, days, and weeks
     */
    public function __call($name, $arguments)
    {
        if (!str_contains($name, 'every')) {
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
    public function at(string $time): static
    {
        $this->getCurrentTask()->addTime($time);
        return $this;
    }

    /**
     * @return TaskPlanner
     */
    public function atMidnight(): static
    {
        return $this->at('24:00');
    }

    /**
     * @return TaskPlanner
     */
    public function atNoon(): static
    {
        return $this->at('12:00');
    }

    /**
     * @param integer|array|string $minute
     * @return $this
     */
    public function atMinute(int|array|string $minute): static
    {
        $this->getCurrentTask()->addMinute($minute);
        return $this;
    }

    /**
     * @param integer|array|string $minute
     * @return TaskPlanner
     */
    public function andAtMinute(int|array|string $minute): static
    {
        return $this->atMinute($minute);
    }

    /**
     * @param integer|array|string $hour
     * @return $this
     */
    public function atHour(int|array|string $hour): static
    {
        $this->getCurrentTask()->addHour($hour);
        return $this;
    }

    /**
     * @param integer|array|string $hour
     * @return TaskPlanner
     */
    public function andAtHour(int|array|string $hour): static
    {
        return $this->atHour($hour);
    }

    /**
     * @param integer|array|string $date
     * @return $this
     */
    public function on(int|array|string $date): static
    {
        $this->getCurrentTask()->addDate($date);
        return $this;
    }

    /**
     * @param integer|array|string $date
     * @return TaskPlanner
     */
    public function andOn(int|array|string $date): static
    {
        return $this->on($date);
    }

    /**
     * @param integer|array|string $day
     * @return $this
     */
    public function onDay(int|array|string $day): static
    {
        $this->getCurrentTask()->addDay($day);
        return $this;
    }

    /**
     * @param integer|array|string $day
     * @return TaskPlanner
     */
    public function andOnDay(int|array|string $day): static
    {
        return $this->onDay($day);
    }

    /**
     * @param integer|array|string $month
     * @return $this
     */
    public function onMonth(int|array|string $month): static
    {
        $this->getCurrentTask()->addMonth($month);
        return $this;
    }

    /**
     * @param integer|array|string $month
     * @return TaskPlanner
     */
    public function andOnMonth(int|array|string $month): static
    {
        return $this->onMonth($month);
    }

    /**
     * @param integer|array|string $weekday
     * @return $this
     */
    public function onWeekday(int|array|string $weekday): static
    {
        $this->getCurrentTask()->addWeekday($weekday);
        return $this;
    }

    /**
     * @param integer|array|string $weekday
     * @return TaskPlanner
     */
    public function andOnWeekday(int|array|string $weekday): static
    {
        return $this->onWeekday($weekday);
    }

    /**
     * @return $this
     */
    public function onSunday(): static
    {
        return $this->onWeekday(0);
    }

    /**
     * @return TaskPlanner
     */
    public function onMonday(): static
    {
        return $this->onWeekday(1);
    }

    /**
     * @return TaskPlanner
     */
    public function onTuesday(): static
    {
        return $this->onWeekday(2);
    }

    /**
     * @return TaskPlanner
     */
    public function onWednesday(): static
    {
        return $this->onWeekday(3);
    }

    /**
     * @return TaskPlanner
     */
    public function onThursday(): static
    {
        return $this->onWeekday(4);
    }

    /**
     * @return TaskPlanner
     */
    public function onFriday(): static
    {
        return $this->onWeekday(5);
    }

    /**
     * @return TaskPlanner
     */
    public function onSaturday(): static
    {
        return $this->onWeekday(6);
    }

    /* Environments */
    /**
     * Restrict task to a specific environment
     * @param string $environment
     * @return $this
     */
    public function onEnvironment(string $environment): static
    {
        $this->getCurrentTask()->addEnvironment(strtoupper($environment));
        return $this;
    }

    public function setEnvironments(array $environment): static
    {
        array_map(function ($value) {
            return strtoupper($value);
        }, $environment);

        $this->getCurrentTask()->setEnvironments($environment);
        return $this;
    }

    /* Alias for onEnvironment() */
    /**
     * @return TaskPlanner
     */
    public function onProductionOnly(): static
    {
        return $this->setEnvironments([static::PRODUCTION_ENVIRONMENT]);
    }

    /* Alias for onEnvironment() */
    /**
     * @return TaskPlanner
     */
    public function onDevelopmentOnly(): static
    {
        return $this->setEnvironments([static::DEVELOPMENT_ENVIRONMENT]);
    }

    /**
     * @param string $task
     * @return $this
     */
    public function followedBy(string $task): static
    {
        $this->getCurrentTask()->addFollowedBy($task);
        return $this;
    }

    /* Running Tasks */
    /**
     * @return bool
     * @throws Exception
     */
    public function runDueTasks(): bool
    {
        return $this->getTaskRunner()->runDueTasks();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function runAllTasks(): bool
    {
        return $this->getTaskRunner()->runAllTasks();
    }

    /**
     * @param $task
     * @return bool
     * @throws Exception
     */
    public function runTask($task): bool
    {
        return $this->getTaskRunner()->runTask($task);
    }

    /**
     * @param array $tasks
     * @return bool
     * @throws Exception
     */
    public function runTasks(array $tasks): bool
    {
        return $this->getTaskRunner()->runTasks($tasks);
    }

    /* Getters and Setters */
    /**
     * @param TaskRunnerInterface $runner
     * @return $this
     */
    public function setTaskRunner(TaskRunnerInterface $runner): static
    {
        $this->taskRunner = $runner;
        return $this;
    }

    /**
     * @return TaskRunnerInterface|GenericTaskRunner
     * @throws Exception
     */
    public function getTaskRunner(): TaskRunnerInterface|GenericTaskRunner
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
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param string $name
     * @return Task
     */
    public function getTask(string $name): Task
    {
        return $this->tasks[$name];
    }

    /**
     * @throws Exception
     */
    public function getDueTasks(): array
    {
        return $this->getTaskRunner()->getDueTasks();
    }

    /**
     * @return string
     */
    public function getNsPrefix(): string
    {
        return $this->nsPrefix;
    }

    /**
     * @param string $nsPrefix
     * @return $this
     */
    public function setNsPrefix(string $nsPrefix): static
    {
        $this->nsPrefix = $nsPrefix;
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
        $this->currentEnvironment = strtoupper($currentEnvironment);
        return $this;
    }

    /* Internal Methods */
    /**
     * @param string $task
     * @param Closure|null $body
     */
    protected function createNewTask(string $task, Closure $body = null)
    {
        $this->tasks[$task] = new Task($task);
        $this->setCurrentTask($task);

        $this->getCurrentTask()->setName(
            ($body) ? $body : $task
        );
    }

    /**
     * @param string $task
     */
    protected function setCurrentTask(string $task)
    {
        $this->currentTask = $this->tasks[$task];
    }

    /**
     * @return Task
     */
    protected function getCurrentTask(): Task
    {
        return $this->currentTask;
    }

    /**
     * @return null|string
     */
    private function getNextClosureIncrement(): ?string
    {
        $this->closureIncrement++;
        return "_task{$this->closureIncrement}";
    }

    /**
     * @return string
     */
    public function getCurrentTime(): string
    {
        return $this->currentTime;
    }

    /**
     * @param mixed $currentTime
     * @return $this
     */
    public function setCurrentTime(mixed $currentTime): static
    {
        $this->currentTime = $currentTime;
        return $this;
    }

    public function flagForTesting()
    {
        $this->testing = true;
    }
}
