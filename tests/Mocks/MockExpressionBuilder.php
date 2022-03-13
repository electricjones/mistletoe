<?php namespace ElectricJones\Mistletoe\Test\Mocks;

use Cron\CronExpression;
use ElectricJones\Mistletoe\ExpressionBuilder;
use ElectricJones\Mistletoe\TaskBag;

/**
 * Class MockExpressionBuilder
 */
class MockExpressionBuilder extends ExpressionBuilder
{
    protected TaskBag $bag;
    protected $testExpression;

    public function __construct($testExpression)
    {
        $this->testExpression = $testExpression;
    }

    /**
     * @param TaskBag|null $bag
     * @return CronExpression
     */
    public function build(TaskBag $bag = null): CronExpression
    {
        return $this->testExpression;
    }

    /**
     * @param TaskBag $bag
     * @return $this
     */
    public function setTaskBag(TaskBag $bag): static
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
     * @return TaskBag
     */
    public function getTaskBag(): TaskBag
    {
        //
    }
}
