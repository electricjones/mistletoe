<?php

namespace ElectricJones\Mistletoe\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunDueCommand
 * @package Mistletoe\Application\Commands
 */
class RunAllCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('run:all')
            ->setDescription('Run all registered tasks')
            ->addArgument('path', InputArgument::OPTIONAL, "Where is your Mistletoe Project File?");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $planner = $this->getTaskPlanner($input->getArgument('path'));
        $planner->runAllTasks();
    }
}
