<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star9-1',
    description: '',
    hidden: false
)]
class Star91Command extends Command
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
                preg_match_all('#(?<number>\-?\d+)#', rtrim($line), $matches);
                $number = $this->computeFinal($matches['number']);
                $total += $number;
            }

            fclose($handle);
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function computeFinal(array $numbers): int
    {
        if ($this->allZero($numbers)) {
            return 0;
        }

        $subNumbers = [];

        for ($i = 0; $i < count($numbers) - 1; $i++) {
            $subNumbers[] = $numbers[$i+1] - $numbers[$i];
        }

        $subAddNumber = $this->computeFinal($subNumbers);

        return end($numbers) + $subAddNumber;
    }

    protected function allZero(array $numbers): bool
    {
        return count(array_filter($numbers, function(int $number) {
            return $number != 0;
        })) == 0;
    }
}
