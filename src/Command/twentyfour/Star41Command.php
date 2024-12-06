<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star41',
    description: '',
    hidden: false
)]
class Star41Command extends Command
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

        $i = 0;

        $rows = [];
        $diag1 = [];
        $diag2 = [];

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                $linePatternCount = $this->findPattern($line);
                $total += $linePatternCount;

                $length = strlen($line) - 1;
                foreach (str_split($line) as $pos => $character) {
                    $rows[$pos][] = $character;
                    $diag1[$length - $pos + $i][] = $character;
                    $diag2[$pos - $length + $i][] = $character;
                }

                $i++;
            }

            fclose($handle);
        }

        foreach($rows as $row)
        {
            $linePatternCount = $this->findPattern(implode($row));
            $total += $linePatternCount;
        }

        foreach($diag1 as $row)
        {
            $linePatternCount = $this->findPattern(implode($row));
            $total += $linePatternCount;
        }

        foreach($diag2 as $row)
        {
            $linePatternCount = $this->findPattern(implode($row));
            $total += $linePatternCount;
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function findPattern(string $line): int
    {
        preg_match_all('/(XMAS)/U', $line, $matchesOrdered);
        preg_match_all('/(SAMX)/U', $line, $matchesReversed);

        return count($matchesOrdered[0]) + count($matchesReversed[0]);
    }
}
