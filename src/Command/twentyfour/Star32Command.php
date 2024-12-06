<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star32',
    description: '',
    hidden: false
)]
class Star32Command extends Command
{

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = 0;

        $handle = fopen($input->getArgument('input'), "r");

        if ($handle) {
            $program = '';
            while (($line = fgets($handle)) !== false) {
                $program .= trim($line);            
            }
            fclose($handle);

            $lineCleaned = $this->cleanLine($program);
            $total += $this->findPattern($lineCleaned);            
        }

        echo $total;

        return Command::SUCCESS;
    }

    protected function cleanLine(string $line): string
    {
        // remove don't parts
        $line = preg_replace('/(don\'t\(\).*do\(\))/U', '', $line);

        if (!preg_match('/don\'t\(\)/', $line)) {
            return $line;
        }

        return preg_replace('/don\'t\(\).*/', '', $line);
    }

    protected function findPattern(string $line): int
    {
        // mul(X,Y)
        // X and Y are each 1-3 digit
        preg_match_all('/mul\((?<number_1>\d{1,3}),(?<number_2>\d{1,3})\)/', $line, $matches);

        $mul = 0;

        foreach (array_keys($matches['number_1']) as $pos) {
            $mul += $matches['number_1'][$pos] * $matches['number_2'][$pos];
        }

        return $mul;
    }    
}
