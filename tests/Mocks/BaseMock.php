<?php
namespace Mistletoe\Test\Mocks;

use Mistletoe\Contracts\RunnableInterface;

abstract class BaseMock implements RunnableInterface
{
    public function doRun($file)
    {
        $myfile = fopen(__DIR__ . "/../temp/{$file}.txt", "w");
        $txt = time();
        fwrite($myfile, $txt);
        fclose($myfile);
    }

    abstract public function run();
}
