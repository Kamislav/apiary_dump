<?php
// src/PublishApiCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use ApiaryDump\ApiaryDocClient;

class PublishApiCommand extends ApiaryCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:publish';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Publish API documentation')
        ->setHelp('This will publish your API documentation on Apiary'
        . "\n\n" . ' Usage: php apiary_cli.php ' . self::$defaultName
        . ' {token} {apiSubdomain} {docFile} [protocol] [baseUrl]')
        ;
        $this->addOption(
          'printCode',
          null,
          null,
          'Print published code to standard output.'
          );
    }

    protected function addRequiredArguments() {
      // arguments from base class first
      parent::addRequiredArguments();

      $this->addArgument('apiSubdomain', InputArgument::REQUIRED, 'API subdomain.')
        ->addArgument('docFile', InputArgument::REQUIRED, 'Path to the file for publishing.')
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      // parse arguments
      $token = $input->getArgument('token');
      $apiSubdomain = $input->getArgument('apiSubdomain');
      $docFile = $input->getArgument('docFile');

      $cli = new ApiaryDocClient($token);
      $response = $cli->parseResponse($cli->publishApiBlueprint($apiSubdomain, $docFile));
      //var_dump($response);

      if (is_array($response)) {
        if ($input->getOption('printCode')) {
          $output->writeln("Published code:\n##############\n\n" . $response[$apiSubdomain]);
        }
        if (! $input->getOption('printCode')) {
          $output->writeln("Published code for API domain: " . $apiSubdomain);
        }
      }

      if (! is_array($response)) {
        $output->writeln($response);
      }
    }
}
