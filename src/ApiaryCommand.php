<?php
// src/ApiaryCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

class ApiaryCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:ApiaryCommand';

    protected function configure()
    {
        $this->setDescription('Apiary command base object.')
        ->setHelp('This is an Apiary command base object.');
        $this->addRequiredArguments();
        $this->addOptionalArguments();
    }

    protected function addRequiredArguments() {
      $this->addArgument('token', InputArgument::REQUIRED, 'Access token.');
    }

    protected function addOptionalArguments() {
      $this->addArgument('protocol', InputArgument::OPTIONAL, 'Request protocol (https://).')
      ->addArgument('baseUrl', InputArgument::OPTIONAL, 'Request base URL (api.apiary.io).')
      ;
    }
}
