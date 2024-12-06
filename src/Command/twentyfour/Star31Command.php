<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star31',
    description: '',
    hidden: false
)]
class Star31Command extends Command
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
            while (($line = fgets($handle)) !== false) {
                $total += $this->findPattern($line);              
            }

            fclose($handle);
        }

        echo $total;

        return Command::SUCCESS;
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
