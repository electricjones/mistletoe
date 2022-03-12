<?php
namespace ElectricJones\Mistletoe;

use Cron\CronExpression;
use ElectricJones\Mistletoe\Contracts\ExpressionBuilderInterface;
use ElectricJones\Mistletoe\Contracts\TaskBagInterface;
use Exception;

/**
 * Class TaskBag
 * @package FBS\Planner
 */
class TaskBag implements TaskBagInterface
{
    /** @var string Task */
    protected string $task;

    /** @var array */
    protected array $environments = [TaskPlanner::PRODUCTION_ENVIRONMENT, TaskPlanner::DEVELOPMENT_ENVIRONMENT];

    /** @var array|string Tasks that must follow this one */
    protected string|array $followedBy = [];

    /** @var  CronExpression */
    protected CronExpression $cronExpression;

    /* Expressions */
    /** @var null|string */
    protected ?string $interval = null; // @daily, @yearly

    /** @var null|string|int|array */
    protected string|int|array|null $minute = null;

    /** @var null|string|int|array */
    protected string|int|array|null $hour = null;

    /** @var null|string|int|array */
    protected string|int|array|null $month = null; // 12

    /** @var null|string|int|array */
    protected string|int|array|null $day = null; // 25

    /** @var null|string|int|array */
    protected string|int|array|null $weekday = null;

    /* Dependencies */
    /** @var ExpressionBuilderInterface */
    protected ExpressionBuilderInterface $expressionBuilder;


    /**
     * TaskBag constructor.
     * @param string|null $task
     * @todo: orders
     */
    public function __construct($task = null)
    {
        if (is_string($task)) {
            $this->task = $task;

        } elseif (is_array($task)) {

            // You may also pass in an array of values at construction
            // They MUST match the property names exactly
            foreach ($task as $key => $value) {
                $this->{'set'.ucfirst($key)}($value);
            }
        }
    }

    /**
     * @return string
     */
    public function getTask(): string
    {
        return $this->task;
    }

    /**
     * @param string $task
     * @return $this
     */
    public function setTask($task): static
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getInterval(): ?string
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     * @return $this
     */
    public function setInterval($interval): static
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * Parses a time from format 12:14
     * @param $time
     * @return $this
     * @throws Exception
     */
    public function setTime($time): static
    {
        $parts = $this->parseTime($time);
        $this->setHour($parts[0]);
        $this->setMinute($parts[1]);

        return $this;
    }

    /**
     * @param string $time
     * @return $this
     */
    public function addTime($time): static
    {
        $parts = $this->parseTime($time);
        $this->addHour($parts[0]);
        $this->addMinute($parts[1]);

        return $this;
    }

    /**
     * Parses a time from formats 11/15 or 11-15
     * @param string $date
     * @return $this
     */
    public function setDate(string $date): static
    {
        $parts = $this->parseDate($date);
        $this->setMonth(intval($parts[0]));
        $this->setDay(intval($parts[1]));

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function addDate(string $date): static
    {
        $parts = $this->parseDate($date);
        $this->addMonth(intval($parts[0]));
        $this->addDay(intval($parts[1]));

        return $this;
    }

    /**
     * @param array|integer|string $month
     * @return $this
     */
    public function setMonth(array|int|string $month): static
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return int|string
     */
    public function getMonth(): int|string
    {
        return $this->month;
    }

    /**
     * @param array|int|string $month
     * @return $this
     */
    public function addMonth(array|int|string $month): static
    {
        $this->appendValue('month', $month);
        return $this;
    }

    /**
     * @param array|integer|string $day
     * @return $this
     */
    public function setDay(array|int|string $day): static
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return array|int|string|null
     */
    public function getDay(): array|int|null|string
    {
        return $this->day;
    }

    /**
     * @param array|int|string $day
     * @return $this
     */
    public function addDay(array|int|string $day): static
    {
        $this->appendValue('day', $day);
        return $this;
    }

    /**
     * @param array|int|string $minute
     * @return $this
     * @throws Exception
     */
    public function setMinute(array|int|string $minute): static
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getMinute(): array|int|null|string
    {
        return $this->minute;
    }

    /**
     * @param array|int|string $minute
     * @return $this
     */
    public function addMinute(array|int|string $minute): static
    {
        $this->appendValue('minute', $minute);
        return $this;
    }

    /**
     * @param array|int|string $hour
     * @return $this
     */
    public function setHour(array|int|string $hour): static
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * @return array|int|string|null
     */
    public function getHour(): array|int|null|string
    {
        return $this->hour;
    }

    /**
     * @param array|int|string $hour
     * @return $this
     */
    public function addHour(array|int|string $hour): static
    {
        $this->appendValue('hour', $hour);
        return $this;
    }

    /**
     * @param array|int|string $weekday
     * @return $this
     */
    public function setWeekday(array|int|string $weekday): static
    {
        $this->weekday = $weekday;
        return $this;
    }

    /**
     * @return array|int|string|null
     */
    public function getWeekday(): array|int|null|string
    {
        return $this->weekday;
    }

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function addWeekday($weekday): static
    {
        $this->appendValue('weekday', $weekday);
        return $this;
    }

    /**
     * @param $environments
     * @return $this
     */
    public function setEnvironments($environments): static
    {
        if (!is_array($environments)) {
            $environments = [$environments];
        }

        $this->environments = $environments;
        return $this;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function addEnvironment(string $environment): static
    {
        $this->environments[] = $environment;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * @param string $task
     * @return $this
     */
    public function addFollowedBy(string $task): static
    {
        $this->followedBy[] = $task;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getFollowedBy(): array|string
    {
        return $this->followedBy;
    }

    /**
     * @param array|string $followedBy
     * @return $this
     */
    public function setFollowedBy(array|string $followedBy): static
    {
        $this->followedBy = $followedBy;
        return $this;
    }

    /**
     * @param string|CronExpression $cronExpression
     * @return $this
     */
    public function setCronExpression(CronExpression|string $cronExpression): static
    {
        $this->cronExpression = ($cronExpression instanceof CronExpression)
            ? $cronExpression
            : CronExpression::factory($cronExpression);

        return $this;
    }

    /**
     * @return CronExpression
     * @todo
     */
    public function getCronExpression(): CronExpression
    {
        return ($this->cronExpression instanceof CronExpression) ? $this->cronExpression : $this->buildExpression();
    }

    /**
     * @return CronExpression
     */
    protected function buildExpression(): CronExpression
    {
        $expression = $this->getExpressionBuilder()->setTaskBag($this)->build();
        $this->setCronExpression($expression);
        return $expression;
    }

    /**
     * @param ExpressionBuilderInterface $expressionBuilder
     */
    public function setExpressionBuilder(ExpressionBuilderInterface $expressionBuilder)
    {
        $this->expressionBuilder = $expressionBuilder;
    }

    /**
     * @return ExpressionBuilder|ExpressionBuilderInterface
     */
    protected function getExpressionBuilder()
    {
        return ($this->expressionBuilder instanceof ExpressionBuilderInterface) ? $this->expressionBuilder : new ExpressionBuilder();
    }


    /* Just passed through to CronExpression */
    /**
     * @param string $currentTime
     * @return bool
     */
    public function isDue(string $currentTime = 'now'): bool
    {
        return $this->getCronExpression()->isDue($currentTime);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @throws Exception
     */
    public function getNextRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false)
    {
        $this->getCronExpression()->getNextRunDate($currentTime, $nth, $allowCurrentDate);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @throws Exception
     */
    public function getPreviousRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false)
    {
        $this->getCronExpression()->getPreviousRunDate($currentTime, $nth, $allowCurrentDate);
    }

    /* Internals */
    /**
     * @param $time
     * @return array
     */
    protected function parseTime($time): array
    {
        return explode(':', $time);
    }

    /**
     * @param string $date
     * @return array
     */
    protected function parseDate(string $date): array
    {
        if (strpos($date, '-')) {
            return explode('-', $date);
        } else {
            return explode('/', $date);
        }
    }

    /**
     * @param string $key
     * @param $value
     * @param string $deliminator
     */
    protected function appendValue(string $key, $value, string $deliminator = ',')
    {
        $value = $this->forceToArray($value);

        foreach ($value as $item) {
            if (!is_null($this->$key)) {
                $this->$key = (string)$this->$key . $deliminator . $item;
            } else {
                $this->$key = (string)$item;
            }
        }
    }

    /**
     * @param $value
     * @return array
     * @internal param $minute
     */
    protected function forceToArray($value): array
    {
        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }
}
