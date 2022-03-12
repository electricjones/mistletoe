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
class ListDueCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this
            ->setName('list:due')
            ->setDescription('List all due tasks')
            ->addArgument('path', InputArgument::OPTIONAL, "Where is your Mistletoe Project File?");;
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $planner = $this->getTaskPlanner($input->getArgument('path'));
        $dueTasks = $planner->getDueTasks();

        $planner->flagForTesting();
        $commands = $planner->runDueTasks();

        $this->listTasks($output, $dueTasks);
        $output->writeln("\nAnd the commands run are: ");

        /** @var array $commands */
        foreach ($commands as $command) {
            $output->writeln($command);
        }
    }
}
