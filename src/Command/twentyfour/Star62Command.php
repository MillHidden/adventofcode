<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star62',
    description: '',
    hidden: false
)]
class Star62Command extends Command
{
    const FREE_BLOCK = '.';
    const OBSTACLE = '#';

    const OBSTRUCTION = 'O';
    const UP = 0;
    const RIGHT = 1;
    const DOWN = 2;
    const LEFT = 3;

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $handle = fopen($input->getArgument('input'), "r");

        $grid = [];
        $pos = null;
        $direction = null;
        $path = [];
        $i = 0;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                foreach (str_split(trim($line)) as $j => $character) {
                    $grid[$i][$j] = $character;

                    if (in_array($character, [self::FREE_BLOCK, self::OBSTACLE])) {
                        continue;
                    }
                    
                    $pos = [$i, $j];

                    $direction = self::UP;
                }
                $i++;
            }

            fclose($handle);
        }

        $maxHeigth = count(array_keys($grid));
        $maxWidth = count($grid[0]);
        $turns = 0;
        $total = 0;
        $step = 0;
        $posObstructions = [];
        while ($this->isInGrid($nextPos = $this->computeNextPos($pos, $direction), $maxHeigth, $maxWidth)) {
            if ($turns === 4) {
                throw new \Exception('arf');
            }

            if ($grid[$nextPos[0]][$nextPos[1]] == self::OBSTACLE) {
                $direction = $this->computeNextDirection($direction);
                $turns++;

                continue;
            }

            if ($turns != 3 && !isset($posObstructions[$nextPos[0]][$nextPos[1]])) {
                $posObstructions[$nextPos[0]][$nextPos[1]] = true;
                $testGrid = $grid;

                $testGrid[$nextPos[0]][$nextPos[1]] = self::OBSTRUCTION;

                if ($this->isLoop($pos, $testGrid, $this->computeNextDirection($direction), $maxHeigth, $maxWidth, $path)) {
                    $total++;
                }
            }

            $path[$pos[0]][$pos[1]][] = $direction;
            $pos = $nextPos;
            
            $turns = 0;
            $step++;
        }

        echo $total;

        return Command::SUCCESS;
    }

    protected function isInGrid(array $pos, string $maxHeight, int $maxWidth): bool
    {
        [$width, $height] = $pos;

        return ($width >= 0 && $width < $maxWidth && $height >= 0 && $height < $maxHeight);
    }

    protected function computeNextPos(array $pos, string $direction): array
    {
        switch ($direction) {
            case self::UP:
                return [$pos[0]-1, $pos[1]];
            case self::DOWN:
                return [$pos[0]+1, $pos[1]];
            case self::LEFT:
                return [$pos[0], $pos[1]-1];
            case self::RIGHT:
                return [$pos[0], $pos[1]+1];
        }
    }

    protected function computeNextDirection(int $direction): int
    {
        return (++$direction) % 4;
    }

    protected function isLoop(array $pos, array $grid, int $direction, int $maxHeigth, int $maxWidth, array $path): bool
    {
        while ($this->isInGrid($nextPos = $this->computeNextPos($pos, $direction), $maxHeigth, $maxWidth)) {
            if (in_array($grid[$nextPos[0]][$nextPos[1]], [self::OBSTACLE, self::OBSTRUCTION])) {
                $direction = $this->computeNextDirection($direction);

                continue;
            }

            if (isset($path[$nextPos[0]][$nextPos[1]]) && in_array($direction, $path[$nextPos[0]][$nextPos[1]])) {

                return true;
            }

            $path[$nextPos[0]][$nextPos[1]][] = $direction;

            $pos = $nextPos;
        }

        return false;
    }
}
