<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star72',
    description: '',
    hidden: false
)]
class Star72Command extends Command
{
    const OPERATOR_COUNT = 3;

    const ADD = '+';
    const MUL = '*';
    const CON = '||';

    const OPERATIONS = [0 => self::ADD, 1 => self::MUL, 2 => self::CON];

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
                [$test, $numbers] = $this->readLine(trim($line));

                if ($this->canProduce($test, $numbers)) {
                    $total += $test;
                }
            }

            fclose($handle);
        }

        echo $total;

        return Command::SUCCESS;
    }

    protected function readLine(string $line): array
    {
        [$test, $numbers] = explode(': ', $line);

        return [intval($test), array_map('intval', explode(' ', $numbers))];
    }

    protected function canProduce(int $test, array $numbers): bool
    {
        $count = count($numbers);

        for ($i = 0; $i <= self::OPERATOR_COUNT**$count - 1; $i++) {
            $operation = $this->generateOperation( str_pad(base_convert($i, 10, self::OPERATOR_COUNT), $count-1, '0', STR_PAD_LEFT));

            if ($this->compute($operation, $numbers, $test) == $test) {
                return true;
            }
        }

        return false;
    }

    protected function generateOperation(string $version): array
    {
        $operation = [];
        for ($i = 0; $i < strlen($version); $i++) {
            $operation[$i] = self::OPERATIONS[$version[$i]];
        }

        return $operation;
    }

    protected function compute(array $operation, array $numbers, int $max): int
    {
        $compute = array_shift($numbers);
        foreach($numbers as $i => $number) {
            switch ($operation[$i]) {
                case self::ADD:
                    $compute += $number;
                    break;
                case self::MUL:
                    $compute *= $number;
                    break;
                case self::CON:
                    $length = strlen($number);
                    $compute = $compute*10**$length + $number;

                    break;
            }

            if ($compute > $max) {
                 return -1;
            }
        }

        return $compute;
    }
}
