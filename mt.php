#!/usr/bin/env php
<?php
require_once("vendor/autoload.php");

use ElectricJones\Mistletoe\Application\Commands\ListAllCommand;
use ElectricJones\Mistletoe\Application\Commands\ListDueCommand;
use ElectricJones\Mistletoe\Application\Commands\RunAllCommand;
use ElectricJones\Mistletoe\Application\Commands\RunDueCommand;
use ElectricJones\Mistletoe\Application\Commands\RunTaskCommand;
use Symfony\Component\Console\Application;

$application = new Application('Mistletoe', '@package_version@');
$application->add(new RunDueCommand());
$application->add(new RunTaskCommand());
$application->add(new RunAllCommand());
$application->add(new ListAllCommand());
$application->add(new ListDueCommand());
$application->run();
