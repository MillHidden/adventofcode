<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star5-1',
    description: '',
    hidden: false
)]
class Star51Command extends Command
{
    const SEEDS_PATTERN = '#(?<seeds>\d+)#';
    const MAPPING_PATTERN = '#(?<destination>\d+)\s+(?<source>\d+)\s+(?<range>\d+)#';

    const STEPS = ['sts', 'stf', 'ftw', 'wtl', 'ltt', 'tth', 'htl'];
    protected array $maps = ['sts' => [], 'stf' => [], 'ftw' => [], 'wtl' => [], 'ltt' => [], 'tth' => [], 'htl' => []];


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
            $seeds = [];

            $line = fgets($handle);
            $line = rtrim($line);
            $seeds = $this->readSeeds($line);
            // empty line
            $line = fgets($handle);

            $stepInc = 0;

            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);                

                if (empty($line)) {
                    $stepInc++;
                    continue;
                }

                if (str_contains($line, 'map')) {
                    continue;
                }

                $this->maps[self::STEPS[$stepInc]][] = $this->readMapping($line);
            }

            $locations = [];

            foreach($seeds as $seed) {
                $locations[] = $this->searchLocation($seed);
                
            }

            echo min($locations);

            fclose($handle);
        }

        return Command::SUCCESS;
    }

    protected function readSeeds(string $line): array
    {
        preg_match_all(self::SEEDS_PATTERN, $line, $matches);

        return $matches['seeds'];
    }

    protected function readMapping(string $line): array
    {
        preg_match(self::MAPPING_PATTERN, $line, $matches);

        return [
            'source' => [(int)$matches['source'], (int)$matches['source'] + (int)$matches['range'] - 1],
            'destination' => [(int)$matches['destination'] , (int)$matches['destination'] + (int)$matches['range'] - 1]
        ];
    }

    protected function searchLocation(int $seed): int
    {
        foreach(self::STEPS as $step) {
            foreach($this->maps[$step] as $map) {
                if ($seed < $map['source'][0] || $seed > $map['source'][1]) {
                    continue;
                }

                $seed = $map['destination'][0] + $seed - $map['source'][0];
                break;
            }
        }

        return $seed;
    }
}
