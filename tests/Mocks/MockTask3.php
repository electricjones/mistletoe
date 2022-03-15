<?php
namespace ElectricJones\Mistletoe\Test\Mocks;

class MockTask3 extends BaseMock
{
    public function run(): void
    {
        $this->doRun('3');
    }
}
