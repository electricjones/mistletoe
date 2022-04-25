<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask1 extends BaseMock
{
    public function run(): void
    {
        $this->doRun('1');
    }
}
