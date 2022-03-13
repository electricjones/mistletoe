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
    // @todo: cannot be an array?
    protected ?string $interval = null; // @daily, @yearly

    /** @var string[] */
    protected array $minutes = [];

    /** @var string[] */
    protected array $hours = [];

    /** @var string[] */
    protected array $months = [];

    /** @var string[] */
    protected array $days = [];

    /** @var string[] */
    protected array $weekdays = [];

    private ?CronExpression $cron_expression = null;

    /**
     * TaskBag constructor.
     * @param string $name // @todo: closure?
     * @param string|null $interval
     * @param int|string|int[]|string[] $minute
     * @param int|string|int[]|string[] $hour
     * @param int|string|int[]|string[] $month
     * @param int|string|int[]|string[] $day
     * @param string|string[] $weekday
     * @param string|string[] $environments
     * @param string|string[] $followedBy
     */
    public function __construct(
        string           $name,
        string|null      $interval = null,
        int|string|array $minute = [],
        int|string|array $hour = [],
        int|string|array $month = [],
        int|string|array $day = [],
        string|array     $weekday = [], // @todo: Enumeration

        string|array     $environments = [],
        string|array     $followedBy = [],
    )
    {
        $this->name = $name;
        $this->interval = $interval;

        $this->minutes = $this->prepareAndValidateValues($minute);
        $this->hours = $this->prepareAndValidateValues($hour);
        $this->months = $this->prepareAndValidateValues($month);
        $this->days = $this->prepareAndValidateValues($day);
        $this->weekdays = $this->prepareAndValidateValues($weekday);
        $this->environments = $this->prepareAndValidateValues($environments);
        $this->followedBy = $this->prepareAndValidateValues($followedBy);
    }

    /* Expressions */
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

    /* Internal Helpers */
    /**
     * @param string[]|int[]|string|int $values
     * @return array
     */
    private function prepareValues(array|string|int $values): array
    {
        return $this->forceIntegersToStrings($this->forceToArray($values));
    }

    /**
     * @param mixed $values
     * @return array
     */
    private function validateValues(mixed $values): array
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException("Items must be arrays");
        }

        foreach ($values as $item) {
            if (!is_string($item)) {
                throw new InvalidArgumentException("Items must be strings");
            }
        }

        return $values;
    }

    /**
     * @param mixed $values
     * @return array
     */
    private function prepareAndValidateValues(mixed $values): array
    {
        return $this->validateValues($this->prepareValues($values));
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

    private function forceIntegersToStrings($value)
    {
        if (is_array($value)) {
            foreach ($value as $i => $item) {
                $value[$i] = $this->forceIntegersToStrings($item);
            }
        }

        if (is_int($value)) {
            return (string)$value;
        }

        return $value;
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


    /* Mutators */
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
    public function setInterval(?string $interval): static
    {
        $this->interval = $interval;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMinutes(): array
    {
        return $this->minutes;
    }

    /**
     * @return string
     */
    public function getMinutesAsString(): string
    {
        return implode(",", $this->getMinutes());
    }

    /**
     * @param int[]|string[]|int|string $minutes
     * @return Task
     */
    public function setMinutes(array|int|string $minutes): Task
    {
        $this->minutes = $this->prepareAndValidateValues($minutes);
        return $this;
    }

    /**
     * @param int|string $minute
     * @return $this
     */
    public function addMinute(int|string $minute): static
    {
        $this->minutes = array_merge($this->prepareAndValidateValues($minute), $this->getMinutes());
        return $this;
    }

    /**
     * @return string[]
     */
    public function getHours(): array
    {
        return $this->hours;
    }

    /**
     * @return string
     */
    public function getHoursAsString(): string
    {
        return implode(",", $this->getHours());
    }

    /**
     * @param string[]|int[]|int|string $hours
     * @return $this
     */
    public function setHours(array|int|string $hours): static
    {
        $this->hours = $this->prepareAndValidateValues($hours);
        return $this;
    }

    /**
     * @param int|string $hour
     * @return $this
     */
    public function addHour(int|string $hour): static
    {
        $this->hours = array_merge($this->prepareAndValidateValues($hour), $this->getHours());
        return $this;
    }

    /**
     * @return string[]
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @return string
     */
    public function getDaysAsString(): string
    {
        return implode(",", $this->getDays());
    }

    /**
     * @param string[]|int[]|string|int $days
     * @return Task
     */
    public function setDays(array|int|string $days): static
    {
        $this->days = $this->prepareAndValidateValues($days);
        return $this;
    }

    /**
     * @param int|string $day
     * @return $this
     */
    public function addDay(int|string $day): static
    {
        $this->days = array_merge($this->prepareAndValidateValues($day), $this->getDays());
        return $this;
    }

    /**
     * @return string[]
     */
    public function getWeekdays(): array
    {
        return $this->weekdays;
    }

    /**
     * @return string
     */
    public function getWeekdaysAsString(): string
    {
        return implode(",", $this->getWeekdays());
    }

    /**
     * @param string[] $weekdays
     * @return Task
     */
    public function setWeekdays(array $weekdays): static
    {
        $this->weekdays = $this->prepareAndValidateValues($weekdays);
        return $this;
    }

    /**
     * @param string $weekday
     * @return $this
     */
    public function addWeekday(string $weekday): static
    {
        $this->weekdays = array_merge($this->prepareAndValidateValues($weekday), $this->getWeekdays());
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMonths(): array
    {
        return $this->months;
    }

    /**
     * @return string
     */
    public function getMonthsAsString(): string
    {
        return implode(",", $this->getMonths());
    }

    /**
     * @param int[]|string[]|int|string $months
     * @return Task
     */
    public function setMonths(array|string|int $months): static
    {
        $this->months = $this->prepareAndValidateValues($months);
        return $this;
    }

    /**
     * @param string|int $month
     * @return $this
     */
    public function addMonth(string|int $month): static
    {
        $this->minutes = array_merge($this->prepareAndValidateValues($month), $this->getMonths());
        return $this;
    }

    /**
     * Parses a time from format 12:14
     * @param string $time
     * @return $this
     * @todo: ?
     */
    public function setTime(string $time): static
    {
        $parts = $this->parseTime($time);
        $this->setHours($parts[0]);
        $this->setMinutes($parts[1]);

        return $this;
    }

    /**
     * @param string $time
     * @return $this
     * @todo: ?
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
     * @todo: ?
     */
    public function setDate(string $date): static
    {
        $parts = $this->parseDate($date);
        $this->setMonths($parts[0]);
        $this->setDays($parts[1]);

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     * @todo: ?
     */
    public function addDate(string $date): static
    {
        $parts = $this->parseDate($date);
        $this->addMonth($parts[0]);
        $this->addDay($parts[1]);

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
     * @param CronExpression|string $cron_expression
     * @return Task
     */
    public function setCronExpression(CronExpression|string $cron_expression): static
    {
        if (is_string($cron_expression)) {
            $cron_expression = new CronExpression($cron_expression);
        }

        $this->cron_expression = $cron_expression;
        return $this;
    }
}
