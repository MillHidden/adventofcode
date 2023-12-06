<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star1',
    description: '',
    hidden: false
)]
class Star1Command extends Command
{
    const NUMBER_AS_WORD = [
        'one' => 'one1e',
        'two' => 'two2o',
        'three' => 'three3e',
        'four' => 'four4r',
        'five' => 'five5e',
        'six' => 'six6x',
        'seven' => 'seven7n',
        'eight' => 'eight8e',
        'nine' => 'nine9e'
    ];

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
                
                $number = $this->compute($line);

                $total += $number;
            }

            fclose($handle);
        }

        echo($total);



        return Command::SUCCESS;
    }

    protected function compute(string $line): int
    {
        $formattedLine = str_replace(array_keys(self::NUMBER_AS_WORD), array_values(self::NUMBER_AS_WORD), $line);
        $firstdigit = $lastdigit = null;

        $i = 0;
        $size = strlen($formattedLine) - 1;

        do {
            if (is_null($firstdigit)) {
                if (is_numeric($formattedLine[$i])) {
                    $firstdigit = (int)$formattedLine[$i];
                }
            }

            if (is_null($lastdigit)) {
                if (is_numeric($formattedLine[$size - $i])) {
                    $lastdigit = (int)$formattedLine[$size - $i];
                }
            }

            $i++;

        } while ($i <= $size && (is_null($firstdigit) || is_null($lastdigit)));

        return $firstdigit * 10 + $lastdigit;
    }   
}
