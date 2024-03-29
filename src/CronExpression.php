<?php

namespace ElectricJones\Mistletoe;

use Cron\CronExpression as BaseCronExpression;

/**
 * Class ExpressionBuilder
 * @package Mistletoe
 */
class CronExpression extends BaseCronExpression
{
    const MINUTE_KEY = 'minutes';
    const HOUR_KEY = 'hours';
    const DAY_KEY = 'days';
    const MONTH_KEY = 'months';
    const WEEKDAY_KEY = 'weekdays';

    /**
     * Builds an expression from the Task
     * @param Task $task
     * @return CronExpression
     */
    static public function from(Task $task): static
    {
        // @Todo: return null if unable
        $parts = [];

        if (static::onlyIntervalIsSet($task)) {
            return new static($task->getInterval());
        }

        // Get with defaults
        $parts[static::MINUTE_KEY] = static::getPartWithDefault($task, static::MINUTE_KEY);
        $parts[static::HOUR_KEY] = static::getPartWithDefault($task, static::HOUR_KEY);
        $parts[static::DAY_KEY] = static::getPartWithDefault($task, static::DAY_KEY);
        $parts[static::MONTH_KEY] = static::getPartWithDefault($task, static::MONTH_KEY);
        $parts[static::WEEKDAY_KEY] = static::getPartWithDefault($task, static::WEEKDAY_KEY);

        /* Are we dealing with outside scenarios? */
        // Are we setting a day and month without a time?
        if (
            ($task->getDays() && $task->getMonths()
                && (!$task->getHours() && !$task->getMinutes()))
        ) {
            // Yes. We don't want it to run every minute!
            $parts[static::MINUTE_KEY] = '0';
            $parts[static::HOUR_KEY] = '0';
        }

        $a = implode(' ', $parts);
        return new static($a);
    }

    /**
     * Is only the interval set in the bag?
     * @param Task $task
     * @return bool
     */
    static private function onlyIntervalIsSet(Task $task): bool
    {
        return $task->getInterval() !== null
            && $task->getMonths() === []
            && $task->getDays() === []
            && $task->getMinutes() === []
            && $task->getHours() === [];
    }

    /**
     * Returns the part from the bag with * as default
     * @param Task $task
     * @param string $part
     * @return string
     */
    static private function getPartWithDefault(Task $task, string $part): string
    {
        $value = $task->{'get' . ucfirst($part)}();
        if (!is_null($value) and !empty($value)) {
            return static::toPart($value);
        } else {
            return '*';
        }
    }

    /**
     * Ensures the parts are cast as strings
     * @param $value
     * @return string
     */
    static private function toPart($value): string
    {
        if ($value === 0) {
            return '0';
        }

        if (is_array($value)) {
            return implode(",", $value);
        }

        return (string)$value;
    }
}
