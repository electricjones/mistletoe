<?php namespace ElectricJones\Mistletoe\Test\Mocks;

use Cron\CronExpression;
use ElectricJones\Mistletoe\ExpressionBuilder;
use ElectricJones\Mistletoe\Task;

/**
 * Class MockExpressionBuilder
 */
class MockExpressionBuilder extends ExpressionBuilder
{
    protected Task $bag;
    protected $testExpression;

    public function __construct($testExpression)
    {
        $this->testExpression = $testExpression;
    }

    /**
     * @param Task|null $bag
     * @return CronExpression
     */
    public function build(Task $bag = null): CronExpression
    {
        return $this->testExpression;
    }

    /**
     * @param Task $bag
     * @return $this
     */
    public function setTaskBag(Task $bag): static
    {
        $this->bag = $bag;
        return $this;
    }

    /**
     * @param string|null $string
     * @return CronExpression
     */
    public function buildFrom(?string $string): CronExpression
    {
        // Not needed
    }

    /**
     * @return Task
     */
    public function getTaskBag(): Task
    {
        //
    }
}
