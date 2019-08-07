<?php
// src/ListApisCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ApiaryDump\ApiaryDocClient;

class ListApisCommand extends ApiaryCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:list';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('List all user API\'s.')
        ->setHelp('This command lists all user API\'s to standard output '
        . 'structured as CSV file. The only required argument is security token.'
        . "\n\n" . ' Usage: php apiary_cli.php ' . self::$defaultName . ' {token} [protocol] [baseUrl]')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $token = $input->getArgument('token');

      $cli = new ApiaryDocClient($token);
      $response = $cli->parseResponse($cli->listApis());
//var_dump($response);
      if (is_string($response)) {
        $output->writeln($response);
      } else if (is_array($response)) {
        $output->writeln("apiName,apiSubdomain,apiIsPrivate,apiIsPublic,apiIsTeam,apiIsPersonal");
        $apiSubdomains = [];
        foreach ($response as $api) {
          $output->writeln("\"$api->apiName\",\"$api->apiSubdomain\",$api->apiIsPrivate,$api->apiIsPublic,$api->apiIsTeam,$api->apiIsPersonal");
          $apiSubdomains[] = $api->apiSubdomain;
        }
      }
    }
}
