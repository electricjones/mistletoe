<?php

namespace ElectricJones\Mistletoe\Contracts;

use Cron\CronExpression;
use ElectricJones\Mistletoe\ExpressionBuilder;
use ElectricJones\Mistletoe\TaskBag;


/**
 * Class TaskBag
 * @package FBS\Planner
 */
interface TaskBagInterface
{
    /**
     * @return string
     */
    public function getTask(): string;

    /**
     * @param $task
     * @return $this
     */
    public function setTask($task): static;

    /**
     * @return null|string
     */
    public function getInterval(): ?string;

    /**
     * @param $interval
     * @return $this
     */
    public function setInterval($interval): static;

    /**
     * Parses a time from format 12:14
     * @param $time
     * @return $this
     */
    public function setTime($time): static;

    /**
     * @param $time
     * @return $this
     */
    public function addTime($time): static;

    /**
     * Parses a time from formats 11/15 or 11-15
     * @param string $date
     * @return $this
     */
    public function setDate(string $date): static;

    /**
     * @param string $date
     * @return $this
     */
    public function addDate(string $date): static;

    /**
     * @param array|int|string $month
     * @return $this
     */
    public function setMonth(array|int|string $month): static;

    /**
     * @return int|null|string|array
     */
    public function getMonth(): array|int|string|null;

    /**
     * @param array|int|string $month
     * @return $this
     */
    public function addMonth(array|int|string $month): static;

    /**
     * @param array|int|string $day
     * @return $this
     */
    public function setDay(array|int|string $day): static;

    /**
     * @return int|null|string|array
     */
    public function getDay(): array|int|string|null;

    /**
     * @param array|int|string $day
     * @return $this
     */
    public function addDay(array|int|string $day): static;

    /**
     * @param array|int|string $minute
     * @return $this
     */
    public function setMinute(array|int|string $minute): static;

    /**
     * @return int|null|string|array
     */
    public function getMinute(): array|int|string|null;

    /**
     * @param array|int|string $minute
     * @return $this
     */
    public function addMinute(array|int|string $minute): static;

    /**
     * @param array|int|string $hour
     * @return $this
     */
    public function setHour(array|int|string $hour): static;

    /**
     * @return int|null|string|array
     */
    public function getHour(): array|int|string|null;

    /**
     * @param array|int|string $hour
     * @return $this
     */
    public function addHour(array|int|string $hour): static;

    /**
     * @param array|int|string $weekday
     * @return $this
     */
    public function setWeekday(array|int|string $weekday): static;

    /**
     * @return int|null|string|array
     */
    public function getWeekday(): array|int|string|null;

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function addWeekday($weekday): static;

    /**
     * @param $environments
     * @return TaskBag
     * @todo: Enumerations
     */
    public function setEnvironments($environments): TaskBag;

    /**
     * @param string $environment
     * @return $this
     */
    public function addEnvironment(string $environment): static;

    /**
     * @return array
     */
    public function getEnvironments(): array;

    /**
     * @param string $task
     * @return $this
     */
    public function addFollowedBy(string $task): static;

    /**
     * @return array|string
     */
    public function getFollowedBy(): array|string;

    /**
     * @param array|string $followedBy
     * @return $this
     */
    public function setFollowedBy(array|string $followedBy): static;

    /**
     * @param string|CronExpression $cronExpression
     * @return $this
     */
    public function setCronExpression(CronExpression|string $cronExpression): static;

    /**
     * @return CronExpression
     */
    public function getCronExpression(): CronExpression;

    /**
     * @param ExpressionBuilder $expressionBuilder
     * @return void
     */
    public function setExpressionBuilder(ExpressionBuilder $expressionBuilder);

    /**
     * @param string $currentTime
     * @return bool
     */
    public function isDue(string $currentTime = 'now'): bool;

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return void
     */
    public function getNextRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false);

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return void
     */
    public function getPreviousRunDate(string $currentTime = 'now', int $nth = 0, bool $allowCurrentDate = false);
}
