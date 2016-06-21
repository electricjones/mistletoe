<?php
namespace Mistletoe\Test\Mocks;

class MockTask2 extends BaseMock
{
    public function run()
    {
        $this->doRun('2');
    }
}
