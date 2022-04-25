<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask5 extends BaseMock
{
    public function run(): void
    {
        $this->doRun('5');
    }
}
