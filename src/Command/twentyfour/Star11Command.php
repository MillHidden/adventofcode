<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star11',
    description: '',
    hidden: false
)]
class Star11Command extends Command
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

        $handle = fopen($input->getArgument('input'), "r");

        $lefts = $rights = [];
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                [$left, $right] = explode('   ', $line);
                $lefts[] = $left;
                $rights[] = $right;
            }

            fclose($handle);
        }

        sort($lefts, SORT_NUMERIC);
        sort($rights, SORT_NUMERIC);

        $total = 0;
        foreach ($lefts as $position => $left) {
            $total += abs($left - $rights[$position]);            
        }

        echo($total);

        return Command::SUCCESS;
    }
}
