<?php

namespace ElectricJones\Mistletoe;

use DateTime;
use Exception;
use InvalidArgumentException;

/**
 * Class TaskBag
 * @package FBS\Planner
 */
class Task
{
    /** @var string Task */
    // @todo: `name` is not the right name here
    protected string $name;

    /** @var array */
    // @todo: enums
    protected array $environments = [TaskPlanner::PRODUCTION_ENVIRONMENT, TaskPlanner::DEVELOPMENT_ENVIRONMENT];

    /** @var string[] Tasks that must follow this one */
    protected array $followedBy = [];

    /* Expressions */
    /** @var null|string */
    // @todo: cannot be an array
    protected ?string $interval = null; // @daily, @yearly

    /** @var string[] */
    protected array $minute = [];

    /** @var string[] */
    protected array $hour = [];

    /** @var string[] */
    protected array $month = [];

    /** @var string[] */
    protected array $day = [];

    /** @var string[] */
    protected array $weekday = [];

    private ?CronExpression $cron_expression = null;

    /**
     * TaskBag constructor.
     * @param string $name
     * @param string|null $interval
     * @param string|array $minute
     * @param string|array $hour
     * @param string|array $month
     * @param string|array $day
     * @param string|array $weekday
     * @param string|array $environments
     * @param string|array $followedBy
     */
    public function __construct(
        string       $name,
        string|null  $interval = null,
        string|array $minute = [],
        string|array $hour = [],
        string|array $month = [],
        string|array $day = [],
        string|array $weekday = [],

        string|array $environments = [],
        string|array $followedBy = [],
    )
    {
        $this->name = $name;
        $this->interval = $interval;

        $this->minute = $this->prepareAndValidate($minute);
        $this->hour = $this->prepareAndValidate($hour);
        $this->month = $this->prepareAndValidate($month);
        $this->day = $this->prepareAndValidate($day);
        $this->weekday = $this->prepareAndValidate($weekday);
        $this->environments = $this->prepareAndValidate($environments);
        $this->followedBy = $this->prepareAndValidate($followedBy);
    }




    /* Just passed through to CronExpression */
    /**
     * @param string $currentTime
     * @return bool
     */
    public function isDue(string $currentTime = 'now'): bool
    {
        return $this->getCronExpression()?->isDue($currentTime);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return DateTime|null
     * @throws Exception
     */
    public function getNextRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false): ?DateTime
    {
        return $this->getCronExpression()?->getNextRunDate($currentTime, $nth, $allowCurrentDate);
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return DateTime|null
     * @throws Exception
     */
    public function getPreviousRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false): ?DateTime
    {
        return $this->getCronExpression()?->getPreviousRunDate($currentTime, $nth, $allowCurrentDate);
    }


    /**
     * @param array|string $items
     * @return array
     */
    private function prepareAndValidate(array|string $items): array
    {
        $items = $this->forceToArray($items);
        foreach ($items as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException("Items must be strings");
            }
        }

        return $items;
    }

    /**
     * @param $time
     * @return array
     */
    private function parseTime($time): array
    {
        return explode(':', $time);
    }

    /**
     * @param string $date
     * @return array
     */
    private function parseDate(string $date): array
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
     */
    private function appendValue(string $key, $value)
    {
        $value = $this->forceToArray($value);

        foreach ($value as $item) {
            if (!is_null($this->$key)) {
                $this->$key = $this->$key . ',' . $item;
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
    private function forceToArray($value): array
    {
        if (!is_array($value)) {
            return [$value];
        }

        return $value;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Task
     */
    public function setName(string $name): Task
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInterval(): ?string
    {
        return $this->interval;
    }

    /**
     * @param string|null $interval
     * @return Task
     */
    public function setInterval(?string $interval): Task
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMinute(): array
    {
        return $this->minute;
    }

    /**
     * @param string[] $minute
     * @return Task
     */
    public function setMinute(array $minute): Task
    {
        $this->minute = $minute;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getHour(): array
    {
        return $this->hour;
    }

    /**
     * @param string[] $hour
     * @return Task
     */
    public function setHour(array $hour): Task
    {
        $this->hour = $hour;
        return $this;
    }

    /**
     * @return string
     */
    public function getMonth(): string
    {
        return implode(",", $this->month);
    }

    /**
     * @param string[] $month
     * @return Task
     */
    public function setMonth(array $month): Task
    {
        $this->month = $month;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDay(): array
    {
        return $this->day;
    }

    /**
     * @param string[] $day
     * @return Task
     */
    public function setDay(array $day): Task
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getWeekday(): array
    {
        return $this->weekday;
    }

    /**
     * @param string[] $weekday
     * @return Task
     */
    public function setWeekday(array $weekday): Task
    {
        $this->weekday = $weekday;
        return $this;
    }

    /**
     * Parses a time from format 12:14
     * @param string $time
     * @return $this
     */
    public function setTime(string $time): static
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
    public function addTime(string $time): static
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
        $this->setMonth($parts[0]);
        $this->setDay($parts[1]);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function addDate(string $date): static
    {
        $parts = $this->parseDate($date);
        $this->addMonth($parts[0]);
        $this->addDay($parts[1]);

        return $this;
    }

    /**
     * @param string $month
     * @return $this
     */
    public function addMonth(string $month): static
    {
        $this->month[] = $month;
        return $this;
    }

    /**
     * @param array|int|string $day
     * @return $this
     */
    public function addDay(array|int|string $day): static
    {
        $this->day[] = $day;
        return $this;
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
    public function addHour(array|int|string $hour): static
    {
        $this->appendValue('hour', $hour);
        return $this;
    }

    /**
     * @param string $weekday
     * @return $this
     */
    public function addWeekday(string $weekday): static
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
     * @return CronExpression|null
     */
    public function getCronExpression(): ?CronExpression
    {
        if (!$this->cron_expression) {
            // @todo: only return if there is something set
            $this->cron_expression = CronExpression::from($this);
        }

        return $this->cron_expression;
    }

    /**
     * @param CronExpression|null $cron_expression
     */
    public function setCronExpression(?CronExpression $cron_expression): void
    {
        $this->cron_expression = $cron_expression;
    }
}
