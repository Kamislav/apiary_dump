<?php
// src/CreateApiCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use ApiaryDump\ApiaryDocClient;

class CreateApiCommand extends ApiaryCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:create';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Create new API project')
        ->setHelp('This command creates a new API project on Apiary'
        . "\n\n" . ' Usage: php apiary_cli.php ' . self::$defaultName
        . ' {token} {type} [public] [desiredName] [code] [protocol] [baseUrl]')
        ;
    }

    protected function addRequiredArguments() {
      // arguments from base class first
      parent::addRequiredArguments();

      $this->addArgument('type', InputArgument::REQUIRED, 'API project type (personal|team).')
      ;
    }

    protected function addOptionalArguments() {
      $this->addArgument('public', InputArgument::OPTIONAL, 'Set TRUE to make project public.')
      ->addArgument('desiredName', InputArgument::OPTIONAL, 'Desired name '
        . 'for the new API project. If the desiredName is already taken, a different '
        . 'domain will be generated for your API Project. It can be later changed '
        . 'in the settings.')
      ->addArgument('code', InputArgument::OPTIONAL, 'API code to publish.')
      ;

      // arguments from the base class last
      parent::addOptionalArguments();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      // parse arguments
      $token = $input->getArgument('token');
      // JSON arguments (with default values)
      $parArr['type'] = $input->getArgument('type');
      $parArr['public'] = $input->getArgument("public") ? $input->getArgument("public") : FALSE;
      $parArr['desiredName'] = $input->getArgument("desiredName") ? $input->getArgument("desiredName") : "apisubdomain";
      $parArr['code'] = $input->getArgument('code') ? $input->getArgument('code') : 'FORMAT: 1A' . "\n" . '# API';
      // format JSON string
      $jsonParams = json_encode($parArr);
//var_dump($jsonParams);
      $cli = new ApiaryDocClient($token);
      $response = $cli->createApiProject($jsonParams);
      //var_dump($response);
      $output->writeln($cli->parseResponse($response));
    }
}
