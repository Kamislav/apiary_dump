<?php
// src/GetApiCodeCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use ApiaryDump\ApiaryDocClient;

/**
 * Class for getting API doc code
 *
 * @var string $token
 * @var array $apiSubdomains
 * @var string $protocol
 * @var string $baseUrl
 *
 * @return GetApiCodeCommand|string
**/
class GetApiCodeCommand extends ApiaryCommand
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:getCode';

    protected function configure()
    {
        parent::configure();

        $this->setDescription('Get API code for passed subdomains')
        ->setHelp('This command lists API codes to standard output '
        . "\n\n" . ' Usage: php apiary_cli.php ' . self::$defaultName
        . ' {token} {subdomains} [protocol] [baseUrl]')
        ;
        $this->addOption(
          'toFile',
          null,
          null,
          'Save output to text files named: "{api-domainname}.code".'
          );
    }

    protected function addRequiredArguments() {
      parent::addRequiredArguments();

      $this->addArgument('apiSubdomains', InputArgument::REQUIRED, 'JSON array '
      . 'string of api subdomains to fetch.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $token = $input->getArgument('token');
      $cli = new ApiaryDocClient($token);
      $blueprints = $cli->fetchBlueprints($input->getArgument('apiSubdomains'));
//var_dump($blueprints);
      foreach ($blueprints as $apiSubdo => $blueprint) {
        if ($input->getOption('toFile')) {
          // write to file
          $filename = './' . $apiSubdo . '.code';
          $result = file_put_contents($filename, $blueprint);
          if ($result === FALSE) {
            $output->writeln('File ' . $apiSubdo . '.code NOT written, something went wrong!');
            break;
          }
          $output->writeln("### subdomain # " . $apiSubdo . " ### written to file");
        }

        if (! $input->getOption('toFile')) {
          // write to stdOut
          $output->writeln("### subdomain # " . $apiSubdo . " ###");
          $output->writeln($blueprint);
        }
      }
    }
}
