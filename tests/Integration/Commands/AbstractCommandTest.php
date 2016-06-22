<?php namespace Mistletoe\Test\Integration\Commands;

use Mistletoe\Application\Commands\AbstractCommand;
use Mistletoe\Application\Commands\ListAllCommand;
use Mistletoe\TaskPlanner;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class AbstractCommandTest extends PHPUnit_Framework_TestCase
{
    static $original_dir;

    public static function setUpBeforeClass()
    {
        static::$original_dir = __DIR__ . "/../../..";
    }

    public function testGetTaskPlannerByDefault()
    {
        chdir(static::$original_dir . "/tests/Mocks"); // Move to the directory of the mistletoe file

        $command = new ConcreteCommand('testing');
        $planner = $command->getTaskPlanner();

        $this->assertEquals($this->getExpected(), $planner, "failed to return correct task planner");
    }

    public function testGetTaskPlannerFromAbsolute()
    {
        chdir(static::$original_dir); // Move to the main directory

        $command = new ConcreteCommand('testing');
        $planner = $command->getTaskPlanner(static::$original_dir . "/tests/Mocks/mistletoe.php");

        $this->assertEquals($this->getExpected(), $planner, "failed to return correct task planner");
    }

    public function testGetTaskPlannerFromRelative()
    {
        chdir(static::$original_dir); // Move to the main directory

        $command = new ConcreteCommand('testing');
        $planner = $command->getTaskPlanner("tests/Mocks/mistletoe.php");

        $this->assertEquals($this->getExpected(), $planner, "failed to return correct task planner");
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Mistletoe config file: /doesnotexist.nope not found.
     */
    public function testNotFoundException()
    {
        chdir(static::$original_dir); // Move to the main directory

        $command = new ConcreteCommand('testing');
        $planner = $command->getTaskPlanner("/doesnotexist.nope");

        $this->assertEquals($this->getExpected(), $planner, "failed to return correct task planner");
    }

    // This uses one of the Commands to do the actual testing
    public function testListTasks()
    {
        $application = new Application();
        $application->add(new ListAllCommand());

        $command = $application->find("list:all");
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'path' => __DIR__ . '/../../Mocks/mistletoe.php'
        ]);

        $output = $commandTester->getDisplay();

        $this->assertContains("Me\\And\\You", $output, "Failed to produce correct output");
        $this->assertContains("Another\\One", $output, "Failed to produce correct output");
        $this->assertContains("Something\\Else", $output, "Failed to produce correct output");
        $this->assertContains("Something\\Else", $output, "Failed to produce correct output");
        $this->assertContains("00 1 * * *", $output, "Failed to produce correct output");
        $this->assertContains("Followed By", $output, "Failed to produce correct output");
    }

    /* Internal Method */
    /**
     * @return TaskPlanner
     */
    private function getExpected()
    {
        return (new TaskPlanner())
            ->add('Me\And\You')->always()
            ->add('Another\One')->daily()->at('1:00')->followedBy('Something\Else');
    }
}

class ConcreteCommand extends AbstractCommand
{
    // To make it testable
    public function listTasks(OutputInterface $output, $tasks)
    {
        return parent::listTasks($output, $tasks);
    }
}
