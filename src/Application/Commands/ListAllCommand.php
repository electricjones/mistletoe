<?php

namespace ElectricJones\Mistletoe\Application\Commands;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RunDueCommand
 * @package Mistletoe\Application\Commands
 */
class ListAllCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('list:all')
            ->setDescription('List all registered tasks')
            ->addArgument('path', InputArgument::OPTIONAL, "Where is your Mistletoe Project File?");;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $planner = $this->getTaskPlanner($input->getArgument('path'));
        $tasks = $planner->getTasks();
        $this->listTasks($output, $tasks);
    }
}
