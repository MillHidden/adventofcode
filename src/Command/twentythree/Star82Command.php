<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star8-2',
    description: '',
    hidden: false
)]
class Star82Command extends Command
{
    const NODE_PATTERN = '#(?<node>\w{3})\s=\s\((?<left>\w{3}),\s(?<right>\w{3})\)#';

    const ENTRANCE = 'A';
    const EXIT = 'Z';

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $handle = fopen($input->getArgument('input'), "r");

        $nodes = [];
        $currentNode = [];
        if ($handle) {
            $instructions = rtrim(fgets($handle));
            fgets($handle);

            while (($line = fgets($handle)) !== false) {
                $node = $this->getNode(rtrim($line));

                $nodes[$node[0]] = ['L' => $node[1], 'R' => $node[2]];

                if ($node[0][2] == self::ENTRANCE) {
                    $currentNodes[] = $node[0];
                }
            }

            fclose($handle);
        }

        $steps = [];
        foreach ($currentNodes as $currentNode) {
            $step = 0;
            $trapped = true;
            while ($trapped) {
                for ($i = 0; $i < strlen($instructions); $i++) {
                    $currentNode = $nodes[$currentNode][$instructions[$i]];
                    $step++;

                    if ($currentNode[2] == self::EXIT) {
                        $steps[] = $step;
                        $trapped = false;
                    }
                }
            }
        }

        $minStep = null;
        foreach($steps as $step) {
            if (is_null($minStep)) {
                $minStep = $step;
                continue;
            }

            $result = 0;
            $res = $step * $minStep;
            while ($step > 1) {
                $r = $step % $minStep;
                if ($r == 0) {
                    $result = $res / $minStep;
                    break;
                }

                $step = $minStep;
                $minStep = $r;
            }

            $minStep = $result;
        }

        echo($minStep);

        return Command::SUCCESS;
    }

    protected function getNode(string $line): array
    {
        preg_match(self::NODE_PATTERN, $line, $matches);

        return [$matches['node'], $matches['left'], $matches['right']];
    }    
}
