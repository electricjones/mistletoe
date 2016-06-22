<?php
namespace Mistletoe;
use Cron\CronExpression;
use Mistletoe\Contracts\ExpressionBuilderInterface;

/**
 * Class ExpressionBuilder
 * @package Mistletoe
 */
class ExpressionBuilder implements ExpressionBuilderInterface
{
    /** @var TaskBag */
    protected $bag;

    /**
     * Builds an expression from the TaskBag
     * @param TaskBag|null $bag
     * @return CronExpression
     */
    public function build(TaskBag $bag = null)
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
    public function buildFrom($string)
    {
        return CronExpression::factory($string);
    }

    /**
     * Set the Task Bag
     * @param TaskBag $bag
     * @return $this
     */
    public function setTaskBag(TaskBag $bag)
    {
        $this->bag = $bag;
        return $this;
    }

    /**
     * @return TaskBag
     */
    public function getTaskBag()
    {
        return $this->bag;
    }

    /**
     * Is only the interval set in the bag?
     * @param TaskBag $bag
     * @return bool
     */
    protected function onlyIntervalIsSet(TaskBag $bag)
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
    protected function toPart($value)
    {
        if ($value === 0) {
            return '0';
        }

        return (string) $value;
    }

    /**
     * Returns the part from the bag with * as default
     * @param string $part
     * @return string
     */
    protected function getPartWithDefault($part)
    {
        $value = $this->bag->{'get'.ucfirst($part)}();
        if (!is_null($value)) {
            return $this->toPart($value);
        } else {
            return '*';
        }
    }
}
