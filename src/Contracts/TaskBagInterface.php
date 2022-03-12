<?php

namespace ElectricJones\Mistletoe\Contracts;

use Cron\CronExpression;
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
    public function getTask();

    /**
     * @param $task
     * @return $this
     */
    public function setTask($task);

    /**
     * @return null|string
     */
    public function getInterval();

    /**
     * @param $interval
     * @return $this
     */
    public function setInterval($interval);

    /**
     * Parses a time from format 12:14
     * @param $time
     * @return $this
     */
    public function setTime($time);

    /**
     * @param $time
     * @return $this
     */
    public function addTime($time);

    /**
     * Parses a time from formats 11/15 or 11-15
     * @param string $date
     * @return $this
     */
    public function setDate($date);

    /**
     * @param string $date
     * @return $this
     */
    public function addDate($date);

    /**
     * @param string|int|array $month
     * @return $this
     */
    public function setMonth($month);

    /**
     * @return int|null|string|array
     */
    public function getMonth();

    /**
     * @param string|int|array $month
     * @return $this
     */
    public function addMonth($month);

    /**
     * @param string|int|array $day
     * @return $this
     */
    public function setDay($day);

    /**
     * @return int|null|string|array
     */
    public function getDay();

    /**
     * @param string|int|array $day
     * @return $this
     */
    public function addDay($day);

    /**
     * @param string|int|array $minute
     * @return $this
     */
    public function setMinute($minute);

    /**
     * @return int|null|string|array
     */
    public function getMinute();

    /**
     * @param string|int|array $minute
     * @return $this
     */
    public function addMinute($minute);

    /**
     * @param string|int|array $hour
     * @return $this
     */
    public function setHour($hour);

    /**
     * @return int|null|string|array
     */
    public function getHour();

    /**
     * @param string|int|array $hour
     * @return $this
     */
    public function addHour($hour);

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function setWeekday($weekday);

    /**
     * @return int|null|string|array
     */
    public function getWeekday();

    /**
     * @param string|int|array $weekday
     * @return $this
     */
    public function addWeekday($weekday);

    /**
     * @return TaskBag
     */
    public function setEnvironments($environments);

    /**
     * @param string $environment
     * @return $this
     */
    public function addEnvironment($environment);

    /**
     * @return array
     */
    public function getEnvironments();

    /**
     * @param string $task
     * @return $this
     */
    public function addFollowedBy($task);

    /**
     * @return array|string
     */
    public function getFollowedBy();

    /**
     * @param string|array $followedBy
     * @return $this
     */
    public function setFollowedBy($followedBy);

    /**
     * @param string|CronExpression $cronExpression
     * @return $this
     */
    public function setCronExpression($cronExpression);

    /**
     * @return CronExpression
     */
    public function getCronExpression();

    /**
     * @param ExpressionBuilderInterface $expressionBuilder
     * @return void
     */
    public function setExpressionBuilder(ExpressionBuilderInterface $expressionBuilder);

    /**
     * @param string $currentTime
     * @return bool
     */
    public function isDue($currentTime = 'now');

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return void
     */
    public function getNextRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false);

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return void
     */
    public function getPreviousRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false);
}
