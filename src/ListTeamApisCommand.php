<?php
// src/ListTeamApisCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use ApiaryDump\ApiaryDocClient;

class ListTeamApisCommand extends ApiaryCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:listTeam';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('List all team API\'s.')
        ->setHelp('This command lists all team API\'s to standard output '
        . 'structured as CSV file. The required arguments are security token and teamId.'
        . "\n\n" . ' Usage: php apiary_cli.php ' . self::$defaultName . ' {token} {teamId} [protocol] [baseUrl]')
        ;
    }

    protected function addRequiredArguments() {
      parent::addRequiredArguments();

      $this->addArgument('teamId', InputArgument::REQUIRED, 'Team ID.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $token = $input->getArgument('token');
      $cli = new ApiaryDocClient($token);
      $response = $cli->listTeamApis($input->getArgument('teamId'));
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
