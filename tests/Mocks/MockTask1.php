<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask1 extends BaseMock
{
    public function run()
    {
        $this->doRun('1');
    }
}
