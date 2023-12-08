<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star4-2',
    description: '',
    hidden: false
)]
class Star42Command extends Command
{
    const SCRATH_CARD_PATTERN = '#Card\s+(?<card_number>\d+): (?<winning_points>[\d\s]+) \| (?<number_list>[\d\s]+)#'; 

    protected array $scratchCards = [];

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $handle = fopen($input->getArgument('input'), "r");

        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $this->computeScratchCards(rtrim($line));
            }

            fclose($handle);
        }
        
        $total = array_reduce($this->scratchCards, function($sum,$number) {
            $sum += $number;
            return $sum;
        });

        echo($total);

        return Command::SUCCESS;
    }

    protected function computeScratchCards(string $line)
    {
        preg_match(self::SCRATH_CARD_PATTERN, $line, $matches);

        $cardNumber = $matches['card_number'];
        // Original scratch card
        if (!array_key_exists($cardNumber , $this->scratchCards)) {
            $this->scratchCards[$cardNumber] = 0;
        }
        $this->scratchCards[$cardNumber]++;

        $winningPoints = explode(' ', $matches["winning_points"]);
        $winningPoints = array_filter($winningPoints);
        $numberList = explode(' ', $matches['number_list']);
        $numberList = array_filter($numberList);

        $winningNumbers = array_intersect($winningPoints, $numberList);

        $winningNumbers = array_filter($winningNumbers);

        if (count($winningNumbers) == 0) {
            return;
        }

        for ($i = 1; $i <= count($winningNumbers); $i++) {
            if (!array_key_exists($cardNumber + $i, $this->scratchCards)) {
                $this->scratchCards[$cardNumber + $i] = 0;
            }
            $this->scratchCards[$cardNumber + $i] += $this->scratchCards[$cardNumber];
        }
    }
}
