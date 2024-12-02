<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star3-1',
    description: '',
    hidden: false
)]
class Star31Command extends Command
{
    const DELIMITER = '.';

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

        $numbers = [];
        $symbols = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);

                list($numbersThisLine, $symbolsThisLine) = $this->readLine($line);
                $numbers[] = $numbersThisLine;
                $symbols[] = $symbolsThisLine;  
            }

            fclose($handle);

            // first line
            foreach ($numbers[0] as $number) {
                $value = key($number);
                $range = $number[$value];
                $toInclude = false;

                foreach ($symbols[0] as $position) {
                    if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                        
                        $toInclude = true;
                    }
                }

                foreach ([1] as $adjLineId) {
                    if (empty($symbols[0 + $adjLineId])) {
                        continue;
                    }

                    foreach ($symbols[0 + $adjLineId] as $position) {
                        if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                            $toInclude = true;
                        }
                    }

                }
                if ($toInclude) {
                    echo($value . ' ');
                    $total += $value;
                }
            }
            echo(PHP_EOL);

            for ($i = 1; $i < count($numbers) - 1; $i++) {
                if (empty($numbers[$i])) {
                    continue;
                }

                foreach ($numbers[$i] as $number) {
                    $value = key($number);
                    $range = $number[$value];
                    $toInclude = false;

                    foreach ($symbols[$i] as $position) {
                        if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                            
                            $toInclude = true;
                        }
                    }

                    foreach ([-1, 1] as $adjLineId) {
                        if (empty($symbols[$i + $adjLineId])) {
                            continue;
                        }

                        foreach ($symbols[$i + $adjLineId] as $position) {
                            if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                                $toInclude = true;
                            }
                        }
                    }
                    if ($toInclude) {
                        echo($value . ' ');
                        $total += $value;
                    }
                }
                echo(PHP_EOL);
            }

            // last Line
            $last = count($numbers) - 1 ;
            foreach ($numbers[$last] as $number) {
                $value = key($number);
                $range = $number[$value];
                $toInclude = false;
                
                foreach ($symbols[$last] as $position) {
                    if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                        
                        $toInclude = true;
                    }
                }

                foreach ([-1] as $adjLineId) {
                    if (empty($symbols[$last + $adjLineId])) {
                        continue;
                    }

                    foreach ($symbols[$last + $adjLineId] as $position) {
                        if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                            $toInclude = true;
                        }
                    }

                }
                if ($toInclude) {
                    echo($value . ' ');
                    $total += $value;
                }
            }
        }
        echo(PHP_EOL);
        echo(PHP_EOL);

        echo($total);

        return Command::SUCCESS;
    }

    protected function readLine(string $line): array
    {
        $numbers = [];
        $symbolsThisLine = [];
        
        $numberFound = false;
        $arrayNumber = [];
        $numberInc = 0;
        $range = [];
        for ($i = 0; $i < strlen($line); $i++) {
            if ($numberFound) {
                if (is_numeric($line[$i])) {
                    $arrayNumber[$numberInc] = (int)$line[$i];
                    $numberInc++;

                    continue;
                }

                $range[1] = $i - 1;

                $number = 0;
                $numberSize = count($arrayNumber);
                // [4, 6, 7]
                // 4 * 10 ^ 2 + 6 * 10 ^  +  7 * 10 ^0;
                for ($j = 0 ; $j < $numberSize; $j++) {
                    $number += $arrayNumber[$j] * 10 ** ($numberSize - $j - 1);
                }

                $numbers[] = [$number => $range];
                $numberFound = false;
                $number = null;
                $arrayNumber = [];
                $numberInc = 0;
                $range = [];

                if ($line[$i] != self::DELIMITER) {
                    $symbolsThisLine[] = $i;

                    continue;
                }
            }

            if (is_numeric($line[$i])) {
                $arrayNumber[$numberInc] = (int)$line[$i];
                $numberInc++;
                $numberFound = true;
                $range[0] = $i;

                continue;
            }

            if ($line[$i] != self::DELIMITER) {
                $symbolsThisLine[] = $i;

                continue;
            }
        }

        if ($numberFound) {
            $range[1] = strlen($line) - 1;

            $number = 0;
            $numberSize = count($arrayNumber);
            // [4, 6, 7]
            // 4 * 10 ^ 2 + 6 * 10 ^  +  7 * 10 ^0;
            for ($j = 0 ; $j < $numberSize; $j++) {
                $number += $arrayNumber[$j] * 10 ** ($numberSize - $j - 1);
            }

            $numbers[] = [$number => $range];
        }

        return [$numbers, $symbolsThisLine];
    }  
}
