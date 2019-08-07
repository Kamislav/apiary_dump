#!/usr/bin/env php
<?php
namespace ApiaryDump;
// application.php

// libraries
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/ApiaryDocClient.php';
// base classes
require_once __DIR__.'/../src/ApiaryCommand.php';
// command classes
require_once __DIR__.'/../src/ListApisCommand.php';
require_once __DIR__.'/../src/ListTeamApisCommand.php';
require_once __DIR__.'/../src/GetApiCodeCommand.php';
require_once __DIR__.'/../src/CreateApiCommand.php';
require_once __DIR__.'/../src/PublishApiCommand.php';

use Symfony\Component\Console\Application;
use App\Command\ListApisCommand;
use App\Command\ListTeamApisCommand;
use App\Command\GetApiCodeCommand;
use App\Command\CreateApiCommand;
use App\Command\PublishApiCommand;

/** @var Application Symphony console application **/
$application = new Application();
$application->add(new ListApisCommand());
$application->add(new ListTeamApisCommand());
$application->add(new GetApiCodeCommand());
$application->add(new CreateApiCommand());
$application->add(new PublishApiCommand());

$application->run();
