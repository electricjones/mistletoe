<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask4 extends BaseMock
{
    public function run(): void
    {
        $this->doRun('4');
    }
}
