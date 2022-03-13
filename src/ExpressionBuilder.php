<?php
namespace ElectricJones\Mistletoe;

use Cron\CronExpression;

/**
 * Class ExpressionBuilder
 * @package Mistletoe
 */
class ExpressionBuilder
{
    /** @var TaskBag */
    protected TaskBag $bag;

    /**
     * Builds an expression from the TaskBag
     * @param TaskBag|null $bag
     * @return CronExpression
     */
    public function build(TaskBag $bag = null): CronExpression
    {
        if ($bag) {
            $this->setTaskBag($bag);
        }
        $parts = [];

        if ($this->onlyIntervalIsSet($this->bag)) {
            $expression = $this->bag->getInterval();

        } else {
            // Get with defaults
            $parts['minute'] = $this->getPartWithDefault('minute');
            $parts['hour'] = $this->getPartWithDefault('hour');
            $parts['date'] = $this->getPartWithDefault('day');
            $parts['month'] = $this->getPartWithDefault('month');
            $parts['weekday'] = $this->getPartWithDefault('weekday');

            /* Are we dealing with outside scenarios? */
            // Are we setting a day and month without a time?
            if (
                ($this->bag->getDay() && $this->bag->getMonth()
                && (!$this->bag->getHour() && !$this->bag->getMinute()))
                ) {
                // Yes. We don't want it to run every minute!
                $parts['minute'] = '0';
                $parts['hour'] = '0';
            }

            // Any other outside scenarios?

            $expression = implode(' ', $parts);
        }

        return $this->buildFrom($expression);
    }

    /**
     * Builds expression from a string expression
     * @param null|string $string
     * @return CronExpression
     */
    public function buildFrom($string): CronExpression
    {
        return new CronExpression($string);
    }

    /**
     * Set the Task Bag
     * @param TaskBag $bag
     * @return $this
     */
    public function setTaskBag(TaskBag $bag): static
    {
        $this->bag = $bag;
        return $this;
    }

    /**
     * @return TaskBag
     */
    public function getTaskBag(): TaskBag
    {
        return $this->bag;
    }

    /**
     * Is only the interval set in the bag?
     * @param TaskBag $bag
     * @return bool
     */
    protected function onlyIntervalIsSet(TaskBag $bag): bool
    {
        return $bag->getInterval() !== null
            && $bag->getMonth() === null
            && $bag->getDay() === null
            && $bag->getMinute() === null
            && $bag->getHour() === null;
    }

    /**
     * Ensures the parts are cast as strings
     * @param $value
     * @return string
     */
    protected function toPart($value): string
    {
        if ($value === 0) {
            return '0';
        }

        return (string)$value;
    }

    /**
     * Returns the part from the bag with * as default
     * @param string $part
     * @return string
     */
    protected function getPartWithDefault(string $part): string
    {
        $value = $this->bag->{'get' . ucfirst($part)}();
        if (!is_null($value)) {
            return $this->toPart($value);
        } else {
            return '*';
        }
    }
}
