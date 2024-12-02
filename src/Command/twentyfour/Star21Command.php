<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star21',
    description: '',
    hidden: false
)]
class Star21Command extends Command
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

        $safeLines = 0;
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if ($this->isSafe($line)) {
                    $safeLines++;
                }
            }

            fclose($handle);
        }

        echo($safeLines);

        return Command::SUCCESS;
    }

    protected function isSafe(string $line): bool
    {
        $levels = explode(' ', $line);
        $variation = null;

        for ($i = 0; $i < count($levels) - 1; $i++) {
            $newVariation = $levels[$i+1] - $levels[$i];

            if ($newVariation === 0) {
                return false;
            }

            if ((abs($newVariation)) > 3) {
                return false;
            }

            if (!is_null($variation) && $variation*$newVariation < 0) {
                return false;
            }

            $variation = $newVariation;
        }

        return true;

    }

    protected function compute(int $previous, int $next): int
    {

    }
}
