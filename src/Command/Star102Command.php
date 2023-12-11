<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star10-2',
    description: '',
    hidden: false
)]
class Star102Command extends Command
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

    protected array $loop = [];

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

        $this->loop[$start[0]][$start[1]] = self::START;
        $this->loop[$currentPosition[0][0]][$currentPosition[0][1]] = $this->grid[$currentPosition[0][0]][$currentPosition[0][1]];
        $this->loop[$currentPosition[1][0]][$currentPosition[1][1]] = $this->grid[$currentPosition[1][0]][$currentPosition[1][1]];

        while(!$closeLoop) {
            $tmpPosition = $currentPosition;
            $currentPosition = [$this->moveLoop($currentPosition[0], $previousPosition[0]), $this->moveLoop($currentPosition[1], $previousPosition[1])];
            $previousPosition = $tmpPosition;

            if ((!array_key_exists($currentPosition[0][0], $this->loop) || !array_key_exists($currentPosition[0][1], $this->loop[$currentPosition[0][0]]))) {
                $this->loop[$currentPosition[0][0]][$currentPosition[0][1]] = $this->grid[$currentPosition[0][0]][$currentPosition[0][1]];
            }
            if ((!array_key_exists($currentPosition[1][0], $this->loop) || !array_key_exists($currentPosition[1][1], $this->loop[$currentPosition[1][0]]))) {
                $this->loop[$currentPosition[1][0]][$currentPosition[1][1]] = $this->grid[$currentPosition[1][0]][$currentPosition[1][1]];
            }
            
            $closeLoop = $currentPosition[0] == $currentPosition[1];
        }

        $inside = 0;
        for ($i = 0; $i < count(array_keys($this->grid)); $i++) {
            for ($j = 0; $j < strlen($this->grid[$i]); $j++) {
                
                if (isset($this->loop[$i][$j])) {
                    // part of loop
                    continue;
                }

                if ($i == $start[0]) {
                    if (($this->countVerticalCross($i, $j) % 2) == 1) {
                        $inside ++;
                    }                    
                } else {
                    if (($this->countHorizontalCross($i, $j) % 2) == 1) {
                        $inside ++;
                    }
                }                
            }
        }
        echo($inside);

        return Command::SUCCESS;
    }

    protected function countVerticalCross(int $x, int $y): int
    {
        $cross = 0;
        $dir = null;
        for ($i = $x - 1; $i >= 0; $i--) {
            if (!isset($this->loop[$i][$y])) {
                continue;
            }


            switch($this->loop[$i][$y]) {
                case self::EW:
                    $cross ++;
                    $dir = null;
                    break;
                case self::NS:
                    break;
                case self::NE:
                    if (!is_null($dir)) {
                        throw new \Exception("Inpossible dir $dir with ".self::NE);
                    }
                    $dir = 'E';
                    break;
                case self::NW:
                    if (!is_null($dir)) {
                        throw new \Exception("Inpossible dir $dir with ".self::NW);
                    }

                    $dir = 'W';
                    break;
                case self::SW:
                    if (is_null($dir)) {
                        throw new \Exception("Inpossible null dir with ".self::SW);
                    }
                    if ($dir == 'E') {
                        $cross ++;
                    }

                    $dir = null;
                    break;
                case self::SE:
                    if (is_null($dir)) {
                        throw new \Exception("Inpossible null dir with ".self::SE);
                    }
                    if ($dir == 'W') {
                        $cross ++;
                    }

                    $dir = null;
                    break;
            }
        }

        return $cross;
    }

    protected function countHorizontalCross(int $x, int $y): int
    {
        $cross = 0;
        $dir = null;
        for ($j = $y;  $j >= 0; $j--) {
            if (!isset($this->loop[$x][$j])) {
                continue;
            }

            switch($this->loop[$x][$j]) {
                case self::EW:
                    break;
                case self::NS:
                    $cross ++;
                    $dir = null;
                    break;
                case self::NE:
                    if (is_null($dir)) {
                        throw new \Exception("Inpossible null dir with ".self::NE);
                    }
                    if ($dir == 'S') {
                        $cross ++;
                    }
                    $dir = null;
                    break;
                case self::NW:
                    if (!is_null($dir)) {
                        throw new \Exception("Inpossible $dir dir with ".self::NW);
                    }

                    $dir = 'N';
                    break;
                case self::SW:
                    if (!is_null($dir)) {
                        throw new \Exception("Inpossible $dir dir with ".self::SW);
                    }

                    $dir = 'S';
                    break;
                case self::SE:
                    if (is_null($dir)) {
                        throw new \Exception("Inpossible null dir with ".self::SE);
                    }

                    if ($dir == 'N') {
                        $cross ++;
                    }

                    $dir = null;
                    break;
            }
        }

        return $cross;
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

        if (count($next) !== 2) {
            throw new \Exception(count($next).' move possibility !!');
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
