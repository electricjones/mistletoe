<?php
namespace Mistletoe\Test\Mocks;

class MockTask1 extends BaseMock
{
    public function run()
    {
        $this->doRun('1');
    }
}
