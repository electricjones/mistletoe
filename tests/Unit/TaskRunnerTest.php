<?php namespace ElectricJones\Mistletoe\Test\Unit;

use Cron\CronExpression;
use ElectricJones\Mistletoe\Command;
use ElectricJones\Mistletoe\Runners\GenericTaskRunner as TaskRunner;
use ElectricJones\Mistletoe\TaskBag;
use ElectricJones\Mistletoe\Test\Mocks\MockTask1;
use ElectricJones\Mistletoe\Test\Mocks\MockTask2;
use ElectricJones\Mistletoe\Test\Mocks\MockTask3;
use PHPUnit\Framework\TestCase;


class TaskRunnerTest extends TestCase
{
    protected $taskBags;
    protected $closureTask1;
    protected $closureTask2;

    public function setUp(): void
    {
        $this->closureTask1 = new Command('sleep 12');
        $this->closureTask2 = function () {
        };

        $this->taskBags = [
            MockTask1::class => new TaskBag([ // is due
                'task'           => MockTask1::class,
                'cronExpression' => CronExpression::factory('30 12 1 1 *'),
                'environments'   => ['PRODUCTION']
            ]),

            MockTask2::class => new TaskBag([
                'task' => MockTask2::class,
                'cronExpression' => CronExpression::factory('45 * * * *'),
                'environments' => ['PRODUCTION', 'DEVELOPMENT']
            ]),

            MockTask3::class => new TaskBag([  // is due, on all environments by default
                'task' => MockTask3::class,
                'cronExpression' => CronExpression::factory('30 * * * *')
            ]),

            // @todo: closure tasks do not work with FlexTaskRunner yet, but Command()s do
            '_task0' => new TaskBag([ // is due
                'task' => $this->closureTask1,
                'cronExpression' => CronExpression::factory('1,30 4,8,12 * 1,6,12 *'),
                'environments' => ['PRODUCTION']
            ]),

            '_task1' => new TaskBag([
                'task' => $this->closureTask2,
                'cronExpression' => CronExpression::factory('1,30 4,8,12 * 2,6,12 *')
            ]),

            '_task2' => new TaskBag([
                'task' => $this->closureTask2,
                'cronExpression' => CronExpression::factory('30 12 1 1 *'),
                'environments' => ['DEVELOPMENT']
            ]),
        ];
    }

    protected function setupRunnerMock()
    {
        // Stub the execute tasks to return a list of build tasks ready for execution
        $runner = $this->getMockBuilder(TaskRunner::class)
            ->setConstructorArgs([$this->taskBags])
            ->setMethods(['executeTasks'])
            ->getMock();

        $runner->expects($this->any())->method('executeTasks')
            ->will($this->returnArgument(0));

        // Setup the task runner
        /**
         * This is to get rid of inspections for phpstorm -- they were getting super annoying!
         * @var $runner TaskRunner
         */
        $runner->setCurrentTime('2016-1-1 12:30'); // for testing purposes
        $runner->setCurrentEnvironment('PRODUCTION');
        return $runner;
    }

    /** @test * */
    public function TestDetermineDueTasks()
    {
        $runner = new TaskRunner($this->taskBags);
        $runner->setCurrentTime('2016-1-1 12:30'); // for testing purposes
        $runner->setCurrentEnvironment('PRODUCTION');

        $tasks = $runner->getDueTasks();

        $this->assertEquals(
            [MockTask1::class => $this->taskBags[MockTask1::class], MockTask3::class => $this->taskBags[MockTask3::class], '_task0' => $this->taskBags['_task0']],
            $tasks,
            "failed to return correct due tasks"
        );
    }

//    /** @test * */
//    THESE WILL BE IMPLEMENTED IN GenericTaskRunner()

//    public function TestLoadAllTasks()
//    {
//        $runner = $this->setupRunnerMock();
//
//        // Execute the test
//        $actual = $runner->runAllTasks();
//        $expected = [
//            'MockTask1' => new MockTask1(),
//            'Some\Spaced\Name\MockTask2' => new MockTask2(),
//            'Some\MockTask3' => new MockTask3(),
//            '_task0' => $this->closureTask1,
//            '_task1' =>  $this->closureTask2,
//            '_task2' =>  $this->closureTask2,
//        ];
//
//        $this->assertEquals(
//            $expected,
//            $actual,
//            "failed to run correct executable tasks"
//        );
//    }

//    /** @test */
//    public function TestLoadSpecificTask()
//    {
//        $runner = $this->setupRunnerMock();
//
//        $actual = $runner->runTask('MockTask1');
//        $expected = [
//            'MockTask1' => new MockTask1(),
//        ];
//
//        $this->assertEquals(
//            $expected,
//            $actual,
//            "failed to run correct specific executable task"
//        );
//    }
//
//    /** @test */
//    public function TestLoadMultipleTasks()
//    {
//        $runner = $this->setupRunnerMock();
//
//        $actual = $runner->runTasks(['MockTask1', '_task0']);
//        $expected = [
//            'MockTask1' => new MockTask1(),
//            '_task0' => $this->closureTask1
//        ];
//
//        $this->assertEquals(
//            $expected,
//            $actual,
//            "failed to run correct multiple executable tasks"
//        );
//    }
//
//    /** @test */
//    public function TestLoadDueTasks()
//    {
//        $runner = $this->setupRunnerMock();
//
//        $actual = $runner->runDueTasks();
//        $expected = [
//            'MockTask1' => new MockTask1(),
//            'Some\\MockTask3' => new MockTask3(),
//            '_task0' => $this->closureTask1
//        ];
//
//        $this->assertEquals(
//            $expected,
//            $actual,
//            "failed to run correct due executable tasks"
//        );
//    }
}
