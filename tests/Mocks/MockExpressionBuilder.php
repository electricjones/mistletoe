<?php namespace ElectricJones\Mistletoe\Test\Mocks;

use ElectricJones\Mistletoe\Contracts\ExpressionBuilderInterface;

/**
 * Class MockExpressionBuilder
 */
class MockExpressionBuilder implements ExpressionBuilderInterface
{
    protected $bag;
    protected $testExpression;

    public function __construct($testExpression)
    {
        $this->testExpression = $testExpression;
    }

    /**
     * @param \ElectricJones\Mistletoe\TaskBag $bag
     * @return \Cron\CronExpression
     */
    public function build(\ElectricJones\Mistletoe\TaskBag $bag = null)
    {
        return $this->testExpression;
    }

    /**
     * @param \ElectricJones\Mistletoe\TaskBag $bag
     * @return $this
     */
    public function setTaskBag(\ElectricJones\Mistletoe\TaskBag $bag)
    {
        $this->bag = $bag;
        return $this;
    }

    /**
     * @param $string
     * @return \Cron\CronExpression
     */
    public function buildFrom($string)
    {
        // Not needed
    }

    /**
     * @return \ElectricJones\Mistletoe\TaskBag
     */
    public function getTaskBag()
    {
        //
    }
}
