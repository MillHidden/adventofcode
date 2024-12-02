<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star11',
    description: '',
    hidden: false
)]
class Star11Command extends Command
{
    const VOID = '.';
    const GALAXY = '#';

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('version') == 'v1') {
            $expansion = 2;
        }

        if ($input->getArgument('version') == 'v2') {
            $expansion = 1000000;
        }

        $cosmicExpansion = ['vertical' => [], 'horizontal' => []];
        $galaxies = [];

        $vertical = [];
        $horizontal = [];

        $horiCount = null;

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            $i = 0;
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);

                if (is_null($horiCount)) {
                    $horiCount = strlen($line);
                }

                for ($j = 0; $j < strlen($line); $j++) {
                    if ($line[$j] == self::GALAXY) {
                        if (!in_array($i, $horizontal)) {
                            $horizontal[] = $i;
                        }
                        if (!in_array($j, $vertical)) {
                            $vertical[] = $j;
                        }
                        $galaxies[] = [$i, $j];
                    } 
                }
                $i++;
            }

            fclose($handle);
        }

        for ($hori = 0; $hori < $i; $hori ++) {
            if (!in_array($hori, $horizontal)) {
                $cosmicExpansion['horizontal'][] = $hori;
            }
        }        

        for ($verti = 0; $verti < $horiCount; $verti ++) {
            if (!in_array($verti, $vertical)) {
                $cosmicExpansion['vertical'][] = $verti;
            }
        }


        $galaxy1Count = 0;
        $total = 0;
        foreach ($galaxies as $galaxy1) {
            $galaxy1Count++;
            $galaxy2Count = 0;
            foreach ($galaxies as $galaxy2) {
                $galaxy2Count++;
                if ($galaxy1 == $galaxy2 || $galaxy2[0] < $galaxy1[0] || ($galaxy2[0] == $galaxy1[0] && $galaxy2[1] < $galaxy1[1])) {
                    continue;
                }

                $horiCosmic = 0;
                $min = min($galaxy1[0], $galaxy2[0]);
                $max = max($galaxy1[0], $galaxy2[0]);
                for ($i = $min; $i < $max; $i ++) {
                    if (in_array($i, $cosmicExpansion['horizontal'])) {
                        $horiCosmic += $expansion - 1;
                    }
                }

                $vertiCosmic = 0;
                $min = min($galaxy1[1], $galaxy2[1]);
                $max = max($galaxy1[1], $galaxy2[1]);
                for ($j = $min; $j < $max; $j ++) {
                    if (in_array($j, $cosmicExpansion['vertical'])) {
                        $vertiCosmic += $expansion - 1;
                    }
                }

                $a = abs($galaxy1[0] - $galaxy2[0] ) + 1 + $horiCosmic;
                $b = abs($galaxy1[1] - $galaxy2[1] ) + 1 + $vertiCosmic;

                $pgcd = $this->pgcd($a, $b);

                $addOneStep = 0;
                if ($pgcd[1] == 0 && $pgcd[0] != 1) {
                    $addOneStep = $pgcd[0] - 1;
                }

                // Nombre de carrés traversés par la diagonale : a+b-pgcd(a,b).
                $total += $a + $b - $pgcd[0] - 1 + $addOneStep;
            }            
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function pgcd(int $a, int $b): array
    { 
        while ($b > 0) { 
            $r = $a % $b; 
            $a = $b; 
            $b = $r; 
        } 

        return [$a, $r];
    }
}
