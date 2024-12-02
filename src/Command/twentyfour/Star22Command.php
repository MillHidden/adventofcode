<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star22',
    description: '',
    hidden: false
)]
class Star22Command extends Command
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
                $levels = explode(' ', $line);
                if ($this->isSafe($levels)) {
                    $safeLines++;
                }
            }

            fclose($handle);
        }

        echo($safeLines);

        return Command::SUCCESS;
    }

    protected function isSafe(array $levels, bool $retried = false): bool
    {
        $variation = null;
        for ($i = 0; $i < count($levels) - 1; $i++) {
            $newVariation = trim($levels[$i+1]) - trim($levels[$i]);

            if (!$this->isValidVariation($newVariation, $variation)) {
                if ($retried) {
                    return false;
                }

                for ($j = 0; $j < count($levels); $j++) {
                    $newLevels = $levels;
                    unset($newLevels[$j]);
                    $newLevels = array_values($newLevels);

                    if ($this->isSafe($newLevels, true)) {
                        return true;
                    }
                }

                return false;                
                
            }

            $variation = $newVariation;
        }

        return true;

    }

    protected function isValidVariation(int $variation, ?int $previousVariation = null): bool
    {
        return $variation !== 0 && (abs($variation)) <= 3 && (is_null($previousVariation) || $previousVariation*$variation > 0);
    }
}
