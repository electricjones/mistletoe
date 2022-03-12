<?php

namespace ElectricJones\Mistletoe\Application\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunDueCommand
 * @package Mistletoe\Application\Commands
 */
class RunDueCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDescription('Run all due tasks')
            ->addArgument('path', InputArgument::OPTIONAL, "Where is your Mistletoe Project File?");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $planner = $this->getTaskPlanner($input->getArgument('path'));
        $planner->runDueTasks();
    }
}
