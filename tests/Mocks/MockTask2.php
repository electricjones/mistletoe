<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask2 extends BaseMock
{
    public function run()
    {
        $this->doRun('2');
    }
}
