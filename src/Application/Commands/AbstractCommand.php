<?php

namespace ElectricJones\Mistletoe\Application\Commands;

use ElectricJones\Mistletoe\CronSchedule;
use ElectricJones\Mistletoe\TaskBag;
use ElectricJones\Mistletoe\TaskPlanner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package Mistletoe\Application\Commands
 */
abstract class AbstractCommand extends Command
{
    /** @var TaskPlanner */
    protected $taskPlanner;

    /**
     * @param OutputInterface $output
     * @param $tasks
     * @return Table
     */
    protected function listTasks(OutputInterface $output, $tasks)
    {
        $rows = [];

        $verbose = ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE);

        /**
         * @var TaskBag $task
         */
        $i = 1;
        foreach ($tasks as $task) {
            $row = [];

            // Number
            $row[] = $i;

            // Name
            $row[] = $task->getTask();

            if ($verbose) {
                // Schedule
                $schedule = CronSchedule::fromCronString((string)$task->getCronExpression());
                $row[] = $schedule->asNaturalLanguage();
            }

            // Cron
            $row[] = (string)$task->getCronExpression();

            // Next Due
            $row[] = (string)$task->getCronExpression()->getNextRunDate()->format('H:i');

            // Environment
            $environments = $task->getEnvironments();
            if ($environments === ['PRODUCTION', 'DEVELOPMENT']) {
                $row[] = 'ALL';
            } else {
                $row[] = implode(', ', $environments);
            }

            // Followed By
            $row[] = implode(" -> ", array_map(function ($task) {
                if ($task instanceof \ElectricJones\Mistletoe\Command) {
                    return $task->getCommand();
                } else {
                    return $task;
                }
            }, $task->getFollowedBy()));

            $rows[] = $row;

            $i++;
        }

        $table = new Table($output);

        if ($verbose) {
            $headers = ['#', 'Task Name', 'Schedule', 'Cron', 'Next Due', 'Environment', 'Followed By'];
        } else {
            $headers = ['#', 'Task Name', 'Cron', 'Next Due', 'Environment', 'Followed By'];
        }

        $table
            ->setHeaders($headers)
            ->setRows($rows);
        $table->render();

        return $table;
    }

    /**
     * @param string|null $path
     * @return TaskPlanner
     * @throws \Exception
     */
    public function getTaskPlanner($path = null)
    {
        // Return a cached instance
        if ($this->taskPlanner) {
            return $this->taskPlanner;
        }

        // Load the config file (which returns a TaskPlanner)
        $cwd = getcwd();

        if ($path !== null) {
            // Were we were given a valid relative path?
            // If not, then we must assume it is an absolute path
            if (file_exists("{$cwd}/{$path}")) {
                $path = "{$cwd}/{$path}";
            }

        } else {
            // If no path specified, try the default
            $path = "{$cwd}/mistletoe.php";
        }

        // Load the TaskPlanner and cache before returning
        return $this->taskPlanner = $this->loadTaskPlanner($path);
    }

    /**
     * Validate a return a TaskPlanner instance from the path
     * of a mistletoe config file.
     *
     * @param $path
     * @return TaskPlanner
     * @throws \Exception
     */
    private function loadTaskPlanner($path)
    {
        if (file_exists($path)) {
            $planner = include($path);

            if (!$planner instanceof TaskPlanner) {
                throw new \Exception("Config file $path did not return a valid TaskPlanner.");
            }

            return $planner;
        }

        throw new \Exception("Mistletoe config file: {$path} not found.");
    }
}
