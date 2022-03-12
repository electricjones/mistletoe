<?php namespace ElectricJones\Mistletoe\Test\Acceptance;

use ElectricJones\Mistletoe\Test\ScenarioSetups;
use Exception;

class TaskPlannerTest extends ScenarioSetups
{
    public function setUp(): void
    {
        parent::setUp();

        if (!file_exists($this->getTestDir() . '/temp/')) {
            mkdir($this->getTestDir() . '/temp/', 0744, true);
        }
        // Clean fixture
        $files = glob($this->getTestDir() . '/temp/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
    }

    public function tearDown(): void
    {
        // Clean fixture
        $files = glob($this->getTestDir() . '/temp/*'); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file)) {
                unlink($file); // delete file
            }
        }
        rmdir($this->getTestDir() . '/temp/');
    }

    /** @test
     * @throws Exception
     */
    public function TestFullShabang()
    {
        $planner = $this->getFullScenario();

        $start_time = time();

        $planner->runDueTasks();

        sleep(3);
        // Get the timestamps of writing
        $files = [1, 2, 3, 4, 5, 6];
        foreach ($files as $file) {
            $path = $this->getTestDir() . '/temp/' . $file . '.txt';
            $file_obj = fopen($path, "r");
            $contents = fread($file_obj, filesize($path));
            fclose($file_obj);

            $timestamp = (int)$contents;

            // Account for file six sleeping for 1 second
            $start_time = ($file === 6) ? $start_time + 1 : $start_time;

            // Check to make sure the files were written at the correct times
            $condition = ($start_time - 2 <= $timestamp) && ($timestamp <= $start_time + 5);
            $this->assertTrue(
                $condition,
                "Failed to write file {$file}");
        }
    }
}

