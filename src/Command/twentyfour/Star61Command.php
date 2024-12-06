<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star61',
    description: '',
    hidden: false
)]
class Star61Command extends Command
{
    const FREE_BLOCK = '.';
    const OBSTACLE = '#';
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
        $step = 0;
        while ($this->isInGrid($nextPos = $this->computeNextPos($pos, $direction), $maxHeigth, $maxWidth)) {
            if ($grid[$nextPos[0]][$nextPos[1]] == self::OBSTACLE) {
                $direction = $this->computeNextDirection($direction);

                continue;
            }

            $pos = $nextPos;

            $path[$pos[0]][$pos[1]] = true;
            $step++;
        }

        $total = 0;
        foreach ($path as $row) {
            $total += count($row);
        }

        echo 'path length :'.$step.PHP_EOL;

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
}
