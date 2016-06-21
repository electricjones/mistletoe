<?php namespace Mistletoe\Test\Mocks;

use Mistletoe\Contracts\ExpressionBuilderInterface;

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
     * @param \Mistletoe\TaskBag $bag
     * @return \Cron\CronExpression
     */
    public function build(\Mistletoe\TaskBag $bag = null)
    {
        return $this->testExpression;
    }

    /**
     * @param \Mistletoe\TaskBag $bag
     * @return $this
     */
    public function setTaskBag(\Mistletoe\TaskBag $bag)
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
     * @return \Mistletoe\TaskBag
     */
    public function getTaskBag()
    {
        //
    }
}
