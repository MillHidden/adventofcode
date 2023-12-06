<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star2',
    description: '',
    hidden: false
)]
class Star2Command extends Command
{
    const GAME_PATTERN = '#Game (?<game_number>\d+): (?<results>.*)#';

    const BLUE_PATTERN = '#(?<blue_count>\d+) blue#';
    const RED_PATTERN = '#(?<red_count>\d+) red#';
    const GREEN_PATTERN = '#(?<green_count>\d+) green#';


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
                $number = $this->compute($line);

                $total += $number;
            }

            fclose($handle);
        }

        echo($total);



        return Command::SUCCESS;
    }

    protected function compute(string $line): int
    {
        preg_match(self::GAME_PATTERN, $line, $matches);

        $results = $matches['results'];

        $minBlue = $minRed = $minGreen = 0;

        foreach (explode(';', $results) as $result) {
            $blueCount = $redCount = $greenCount = 0;

            preg_match(self::BLUE_PATTERN, $result, 
            $matches);
            if (array_key_exists('blue_count', $matches)) {
                $blueCount = (int)$matches['blue_count'];
                if ($blueCount > $minBlue) {
                    $minBlue = $blueCount;
                }
            }

            preg_match(self::RED_PATTERN, $result, 
            $matches);
            if (array_key_exists('red_count', $matches)) {
                $redCount = (int)$matches['red_count'];
                if ($redCount > $minRed) {
                    $minRed = $redCount;
                }
            }

            preg_match(self::GREEN_PATTERN, $result, 
            $matches);
            if (array_key_exists('green_count', $matches)) {
                $greenCount = (int)$matches['green_count'];
                if ($greenCount > $minGreen) {
                    $minGreen = $greenCount;
                }
            }
        }

        return $minBlue * $minRed * $minGreen;
    }   
}
