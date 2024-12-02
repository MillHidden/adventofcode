<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star10-1',
    description: '',
    hidden: false
)]
class Star101Command extends Command
{
    const NS = '|';
    const EW = '-';
    const NE = 'L';
    const NW = 'J';
    const SW = '7';
    const SE = 'F';
    const GROUND = '.';
    const START = 'S';

    protected array $grid = [];

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $start = null;
        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            $i = 0;
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);
                $this->grid[$i] = $line;

                if (is_null($start) && str_contains($line, self::START)) {
                    $start = [$i, strpos($line, self::START)];
                }
                $i++;
            }

            fclose($handle);
        }

        $closeLoop = false;
        $previousPosition = [$start, $start];
        $currentPosition = $this->findFirstMove($start);

        $step = 1;
        while(!$closeLoop) {
            $tmpPosition = $currentPosition;
            $currentPosition = [$this->moveLoop($currentPosition[0], $previousPosition[0]), $this->moveLoop($currentPosition[1], $previousPosition[1])];
            $previousPosition = $tmpPosition;
            
            $closeLoop = $currentPosition[0] == $currentPosition[1];
            $step++;
        }

        echo($step);

        return Command::SUCCESS;
    }

    protected function findFirstMove(array $position): array
    {
        $next = [];
        $first = 0;
        foreach ([[-1, 0], [0, -1], [0, 1], [1, 0]] as $adj) {
            if ($position[0] + $adj[0] < 0) {
                continue;
            }
            if ($position[1] + $adj[1] < 0) {
                continue;
            }

            if ($adj[0] == 0) {
                if (($adj[1] == -1 && in_array($this->grid[$position[0]][$position[1] + $adj[1]], [self::EW, self::NE, self::SE])) || ($adj[1] == 1 && in_array($this->grid[$position[0]][$position[1] + $adj[1]], [self::EW, self::NW, self::SW]))) {
                    $next[$first] = [$position[0], $position[1] + $adj[1]];
                    $first ++;
                }
            }

            if (($adj[0] == -1 && in_array($this->grid[$position[0] + $adj[0]][$position[1]], [self::NS, self::SW, self::SE])) || ($adj[0] == 1 && in_array($this->grid[$position[0] + $adj[0]][$position[1]], [self::NS, self::NE, self::NW]))) {
                $next[$first] = [$position[0] + $adj[0], $position[1]];
                $first ++;
            }            
        }

        return $next;
    }

    protected function moveLoop(array $position, array $previousPosition): array
    {
        switch ($this->grid[$position[0]][$position[1]]) {
            case self::NS:
                if ($previousPosition[0] == $position[0] - 1) {
                    return [$position[0] + 1, $position[1]];
                }

                return [$position[0] - 1, $position[1]];
            case self::EW:
                if ($previousPosition[1] == $position[1] - 1) {
                    return [$position[0], $position[1] + 1];
                }

                return [$position[0], $position[1] - 1];
            case self::NE:
                if ($previousPosition[0] == $position[0]) {
                    return [$position[0] - 1, $position[1]];
                }

                return [$position[0], $position[1] + 1];
            case self::NW:
                if ($previousPosition[0] == $position[0]) {
                    return [$position[0] - 1 , $position[1]];
                }

                return [$position[0], $position[1] - 1];
            case self::SW:
                if ($previousPosition[0] == $position[0]) {
                    return [$position[0] + 1, $position[1]];
                }

                return [$position[0], $position[1] - 1];
            case self::SE:
                if ($previousPosition[0] == $position[0]) {
                    return [$position[0] + 1, $position[1]];
                }

                return [$position[0], $position[1] + 1];
        }

        return [];
    }
}
