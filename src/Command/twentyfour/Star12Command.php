<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star12',
    description: '',
    hidden: false
)]
class Star12Command extends Command
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
                $left = trim($left);
                $right = trim($right);
                $lefts[] = $left;
                if (!array_key_exists($right, $rights)) {
                    $rights[$right] = 0;
                }
                $rights[$right]++;
            }

            fclose($handle);
        }

        $total = 0;
        foreach ($lefts as $left) {
            if (!array_key_exists($left, $rights)) {
                continue;
            }

            $total += ($left * $rights[$left]);
        }

        echo($total);

        return Command::SUCCESS;
    }
}
