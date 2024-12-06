<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star42',
    description: '',
    hidden: false
)]
class Star42Command extends Command
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

        $grid = [];
        $toCheck = [];
        $i = 0;

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                foreach (str_split(trim($line)) as $j => $character) {
                    $grid[$i][$j] = $character;
                    if ($character == 'A') {
                        $toCheck[] = [$i, $j];
                    }
                }
                $i++;
            }

            fclose($handle);
        }

        foreach ($toCheck as $pos) {
            if ($this->checkCross($pos, $grid)) {
                $total ++;
            }
        }
        
        echo $total;

        return Command::SUCCESS;
    }

    protected function checkCross(array $pos, array $grid): bool
    {
        foreach ([
                [[$pos[0] - 1, $pos[1] - 1], [$pos[0] + 1, $pos[1] + 1]], 
                [[$pos[0] - 1, $pos[1] + 1], [$pos[0] + 1, $pos[1] - 1]]
            ] as $charactersPos) {
            $characters = [];
            foreach ($charactersPos as $i => $characterPos) {
                if (!isset($grid[$characterPos[0]][$characterPos[1]])) {
                    return false;
                }
                $characters[$i] = $grid[$characterPos[0]][$characterPos[1]];
            }

            if ($characters != ['M', 'S'] && $characters != ['S', 'M']) {
                return false;
            }
        }

        return true;
    }
}
