<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star3-2',
    description: '',
    hidden: false
)]
class Star32Command extends Command
{
    const DELIMITER = '.';

    const GEAR_SYMBOL = '*';

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
        $gears = [];

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);

                list($numbersThisLine, $gearsThisLine) = $this->readLine($line);
                $numbers[] = $numbersThisLine;
                $gears[] = $gearsThisLine;  
            }

            fclose($handle);

            // first line
            foreach ($gears[0] as $position) {
                $adjacentNumbers = [];
                if (count($numbers[0]) >= 2) {
                    foreach ($numbers[0] as $number) {
                        $value = key($number);
                        $range = $number[$value];

                        if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                            $adjacentNumbers[] = $value;
                        }
                    }
                }
                
                foreach ($numbers[1] as $number) {
                    $value = key($number);
                    $range = $number[$value];
                    if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                        $adjacentNumbers[] = $value;
                    }
                }

                if (count($adjacentNumbers) == 2)  {
                    $total += $adjacentNumbers[0] * $adjacentNumbers[1];
                    echo($adjacentNumbers[0].self::GEAR_SYMBOL.$adjacentNumbers[1]. ' ');
                }
            }
            echo(PHP_EOL);

            for ($i = 1; $i < count($gears) - 1; $i++) {
                foreach($gears[$i] as $position) {
                    $adjacentNumbers = [];
                    if (count($numbers[$i]) >= 2) {
                        foreach ($numbers[$i] as $number) {
                            $value = key($number);
                            $range = $number[$value];
    
                            if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                                $adjacentNumbers[] = $value;
                            }
                        }
                    }

                    foreach ([-1, 1] as $adjLineId) {
                        foreach ($numbers[$i + $adjLineId] as $number) {
                            $value = key($number);
                            $range = $number[$value];
                            if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                                $adjacentNumbers[] = $value;
                            }
                        }
                    } 

                    if (count($adjacentNumbers) == 2)  {
                        echo($adjacentNumbers[0].self::GEAR_SYMBOL.$adjacentNumbers[1]. ' ');
                        $total += $adjacentNumbers[0] * $adjacentNumbers[1];
                    }
                }
                echo(PHP_EOL);
            }

            // last Line
            $last = count($numbers) - 1 ;
            foreach ($gears[$last] as $position) {
                $adjacentNumbers = [];
                if (count($numbers[$last]) >= 2) {
                    foreach ($numbers[$last] as $number) {
                        $value = key($number);
                        $range = $number[$value];

                        if ($position == $range[1] + 1 || $position == $range[0] - 1 ) {
                            $adjacentNumbers[] = $value;
                        }
                    }
                }

                foreach ($numbers[$last - 1] as $number) {
                    $value = key($number);
                    $range = $number[$value];
                    if ($position >= $range[0] - 1 && $position <= $range[1] + 1 ) {
                        $adjacentNumbers[] = $value;
                    }
                }

                if (count($adjacentNumbers) == 2)  {
                    echo($adjacentNumbers[0].self::GEAR_SYMBOL.$adjacentNumbers[1]. ' ');
                    $total += $adjacentNumbers[0] * $adjacentNumbers[1];
                }
            }
            echo(PHP_EOL);
        }
        echo(PHP_EOL);
        echo(PHP_EOL);

        echo($total);

        return Command::SUCCESS;
    }

    protected function readLine(string $line): array
    {
        $numbers = [];
        $gears = [];
        
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

                if ($line[$i] == self::GEAR_SYMBOL) {
                    $gears[] = $i;

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

            if ($line[$i] == self::GEAR_SYMBOL) {
                $gears[] = $i;

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

        return [$numbers, $gears];
    }  
}
