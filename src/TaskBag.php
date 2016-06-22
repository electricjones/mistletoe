<?php
namespace Mistletoe;
use Cron\CronExpression;
use Mistletoe\Contracts\ExpressionBuilderInterface;
use Mistletoe\Contracts\TaskBagInterface;

/**
 * Class TaskBag
 * @package FBS\Planner
 */
class TaskBag implements TaskBagInterface
{
    /** @var string Task */
    protected $task;

    /** @var array */
    protected $environments = [TaskPlanner::PRODUCTION_ENVIRONMENT, TaskPlanner::DEVELOPMENT_ENVIRONMENT];

    /** @var array|string Tasks that must follow this one */
    protected $followedBy = [];

    /** @var  CronExpression */
    protected $cronExpression;

    /* Expressions */
    /** @var bool|string */
    protected $interval = false; // @daily, @yearly

    /** @var null|string|int|array */
    protected $minute = null;

    /** @var null|string|int|array */
    protected $hour = null;

    /** @var null|string|int|array */
    protected $month = null; // 12

    /** @var null|string|int|array */
    protected $day = null; // 25

    /** @var null|string|int|array */
    protected $weekday = null;

    /* Dependencies */
    /** @var ExpressionBuilderInterface */
    protected $expressionBuilder;


    /**
     * TaskBag constructor.
     * @param string $task
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
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param string $task
     * @return $this
     */
    public function setTask($task)
    {
        $this->task = $task;
        return $this;
    }

    /**
     * @return bool|string
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * @param string $interval
     * @return $this
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * Parses a time from format 12:14
     * @param $time
     * @return $this
     */
    public function setTime($time)
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
    public function addTime($time)
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
    public function setDate($date)
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
    public function addDate($date)
    {
        return $this->setDate($date);
    }

    /**
     * @param integer $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @param string|int|array $month
     * @return $this
     */
    public function addMonth($month)
    {
        $this->appendValue('month', $month);
        return $this;
    }

    /**
     * @param integer $day
     * @return $this
     */
    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param string|int|array $day
     * @return $this
     */
    public function addDay($day)
    {
        $this->appendValue('day', $day);
        return $this;
    }

    /**
     * @param string|int|array $minute
     * @return $this
     * @throws \Exception
     */
    public function setMinute($minute)
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getMinute()
    {
        return $this->minute;
    }

    /**
     * @param string|int|array $minute
     * @return $this
     */
    public function addMinute($minute)
    {
        $this->appendValue('minute', $minute);
        return $this;
    }

    /**
     * @param string|int|array $hour
     * @return $this
     */
    public function setHour($hour)
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getHour()
    {
        return $this->hour;
    }

    /**
     * @param string|int|array $hour
     * @return $this
     */
    public function addHour($hour)
    {
        $this->appendValue('hour', $hour);
        return $this;
    }

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function setWeekday($weekday)
    {
        $this->weekday = $weekday;
        return $this;
    }

    /**
     * @return int|null|string
     */
    public function getWeekday()
    {
        return $this->weekday;
    }

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function addWeekday($weekday)
    {
        $this->appendValue('weekday', $weekday);
        return $this;
    }

    public function setEnvironments($environments)
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
    public function addEnvironment($environment)
    {
        $this->environments[] = $environment;
        return $this;
    }

    /**
     * @return array
     */
    public function getEnvironments()
    {
        return $this->environments;
    }

    /**
     * @param string $task
     * @return $this
     */
    public function addFollowedBy($task)
    {
        $this->followedBy[] = $task;
        return $this;
    }

    /**
     * @return array
     */
    public function getFollowedBy()
    {
        return $this->followedBy;
    }

    /**
     * @param string $followedBy
     * @return $this
     */
    public function setFollowedBy($followedBy)
    {
        $this->followedBy = $followedBy;
        return $this;
    }

    /**
     * @param string|CronExpression $cronExpression
     * @return $this
     */
    public function setCronExpression($cronExpression)
    {
        $this->cronExpression = ($cronExpression instanceof CronExpression)
            ? $cronExpression
            : CronExpression::factory($cronExpression);

        return $this;
    }

    /**
     * @return CronExpression
     */
    public function getCronExpression()
    {
        return ($this->cronExpression instanceof CronExpression) ? $this->cronExpression : $this->buildExpression();
    }

    /**
     * @return CronExpression
     */
    protected function buildExpression()
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
    public function isDue($currentTime = 'now')
    {
        return $this->getCronExpression()->isDue($currentTime);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     */
    public function getNextRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false)
    {
        $this->getCronExpression()->getNextRunDate($currentTime, $nth, $allowCurrentDate);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     */
    public function getPreviousRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false)
    {
        $this->getCronExpression()->getPreviousRunDate($currentTime, $nth, $allowCurrentDate);
    }

    /* Internals */
    /**
     * @param $time
     * @return array
     */
    protected function parseTime($time)
    {
        $parts = explode(':', $time);
        return $parts;
    }

    /**
     * @param string $date
     * @return array
     */
    protected function parseDate($date)
    {
        if (strpos($date, '-')) {
            $parts = explode('-', $date);
            return $parts;
        } else {
            $parts = explode('/', $date);
            return $parts;
        }
    }

    /**
     * @param string $key
     * @param $value
     * @param string $deliminator
     */
    protected function appendValue($key, $value, $deliminator = ',')
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
    protected function forceToArray($value)
    {
        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }
}
