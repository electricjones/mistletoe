#!/usr/bin/env php
<?php
require_once("vendor/autoload.php");

use Mistletoe\Application\Commands\ListAllCommand;
use Mistletoe\Application\Commands\ListDueCommand;
use Mistletoe\Application\Commands\RunAllCommand;
use Mistletoe\Application\Commands\RunDueCommand;
use Mistletoe\Application\Commands\RunTaskCommand;
use Symfony\Component\Console\Application;

$application = new Application('Mistletoe', '@package_version@');
$application->add(new RunDueCommand());
$application->add(new RunTaskCommand());
$application->add(new RunAllCommand());
$application->add(new ListAllCommand());
$application->add(new ListDueCommand());
$application->run();