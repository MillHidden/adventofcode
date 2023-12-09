<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star8-1',
    description: '',
    hidden: false
)]
class Star81Command extends Command
{
    const NODE_PATTERN = '#(?<node>\w{3})\s=\s\((?<left>\w{3}),\s(?<right>\w{3})\)#';
    
    const ENTRANCE = 'AAA';
    const EXIT = 'ZZZ';


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
        if ($handle) {
            $instructions = rtrim(fgets($handle));
            fgets($handle);

            while (($line = fgets($handle)) !== false) {
                $node = $this->getNode(rtrim($line));

                $nodes[$node[0]] = ['L' => $node[1], 'R' => $node[2]];
            }

            fclose($handle);
        }

        $step = 0;
        $currentNode = self::ENTRANCE;
        $trapped = true;
        while ($trapped) {
            for ($i = 0; $i < strlen($instructions); $i++) {
                echo($currentNode.' '.$instructions[$i].' ');
                $currentNode = $nodes[$currentNode][$instructions[$i]];
                echo(' -> '.$currentNode.PHP_EOL);

                $step++;

                if ($currentNode == self::EXIT) {
                    echo($step);
                    $trapped = false;
                }
            }
        }

        return Command::SUCCESS;
    }

    protected function getNode(string $line): array
    {
        preg_match(self::NODE_PATTERN, $line, $matches);

        return [$matches['node'], $matches['left'], $matches['right']];
    }    
}
