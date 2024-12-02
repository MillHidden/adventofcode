<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star6-2',
    description: '',
    hidden: false
)]
class Star62Command extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = 1;

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            preg_match_all('#(?<time>\d+)#', rtrim(fgets($handle)), $matches);
            $times = $matches['time'];
            $time = '';
            foreach ($times as $timePart) {
                $time .= $timePart;
            }

            preg_match_all('#(?<distance>\d+)#', rtrim(fgets($handle)), $matches);
            fclose($handle);
            $distances = $matches['distance'];
            $distance = '';
            foreach ($distances as $distancePart) {
                $distance .= $distancePart;
            }

            $bests = $this->computeBests((int)$time, (int)$distance);
            
            if ($bests > 0) {
                echo($bests.PHP_EOL);
            }            
        }

        return Command::SUCCESS;
    }

    protected function computeBests(int $time, int $distance): int
    {
        $d = $time ** 2 - 4 * $distance;

        $s1 = (-sqrt($d) + $time) / 2;
        $min = ceil($s1);

        if ($min == $s1) {
            $min ++;
        }

        $s2 = (sqrt($d) + $time) / 2;
        $max = floor($s2);

        if ($max == $s2) {
            $max --;
        }
        
        $bests = $max - $min + 1;

        return $bests;
    }   
}
