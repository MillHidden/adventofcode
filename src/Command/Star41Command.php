<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star4-1',
    description: '',
    hidden: false
)]
class Star41Command extends Command
{
    const SCRATH_CARD_PATTERN = '#Card\s+\d+: (?<winning_points>[\d\s]+) \| (?<number_list>[\d\s]+)#'; 

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

        if ($handle) {
            while (($line = fgets($handle)) !== false) {

                $total += $this->computeWinningPoints(rtrim($line));

            }

            fclose($handle);
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function computeWinningPoints(string $line): int
    {
        preg_match(self::SCRATH_CARD_PATTERN, $line, $matches);

        $winningPoints = explode(' ', $matches["winning_points"]);
        $winningPoints = array_filter($winningPoints);
        $numberList = explode(' ', $matches['number_list']);
        $numberList = array_filter($numberList);

        $winningNumbers = array_intersect($winningPoints, $numberList);

        $winningNumbers = array_filter($winningNumbers);

        if (count($winningNumbers) == 0) {
            return 0;
        }

        return 2 ** (count($winningNumbers) - 1);
    }
}
