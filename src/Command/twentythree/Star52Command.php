<?php

namespace App\Command;

use Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star5-2',
    description: '',
    hidden: false
)]
class Star52Command extends Command
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
        $start = microtime(true);
        $output->writeln('<comment>==> Memory usage: '.round(memory_get_peak_usage(true)/1000000, 1).' Mo</comment>');
        $handle = fopen($input->getArgument('input'), "r");

        if ($handle) {
            $seedsLine = fgets($handle);
            $seedsLine = rtrim($seedsLine);
            // empty line
            $line = fgets($handle);

            $stepInc = 0;

            $currentMap = [];

            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);                

                if (empty($line)) {
                    uasort($currentMap, function(array $a, array $b) {
                        return $a['destination'][0] <=> $b['destination'][1];
                    });

                    $min = 0;
                    foreach($currentMap as $map) {
                        if ($map['destination'][0] > $min) {
                            $this->maps[self::STEPS[$stepInc]][] = [
                                'source' => [$min, $map['destination'][0] -1],
                                'destination' => [$min, $map['destination'][0] -1]
                            ];
                        }
                        $this->maps[self::STEPS[$stepInc]][] = $map;
                        $min = $map['destination'][1] + 1;
                    }
                    $stepInc++;

                    $currentMap = [];
                    continue;
                }

                if (str_contains($line, 'map')) {
                    continue;
                }

                $currentMap[] = $this->readMapping($line);
            }
            fclose($handle);

            uasort($currentMap, function(array $a, array $b) {
                return $a['destination'][0] <=> $b['destination'][1];
            });

            $min = 0;
            foreach ($currentMap as $map) {
                if ($map['destination'][0] > $min) {
                    $this->maps[self::STEPS[$stepInc]][] = [
                        'source' => [$min, $map['destination'][0] -1],
                        'destination' => [$min, $map['destination'][0] -1]
                    ];
                }
                $this->maps[self::STEPS[$stepInc]][] = $map;
                $min = $map['destination'][1] + 1;
            }

            $seedsRange = $this->getSeedsRange($seedsLine);
            uasort($seedsRange, function(array $a, array $b) {
                return $a[0] <=> $b[1];
            });

            foreach($this->getSeedsToCheck($this->maps['htl'][0]['source'][0], $this->maps['htl'][0]['source'][1], 5) as $minimalSeedRange) {
                foreach($seedsRange as $seedRange) {                
                    if ($seedRange[1] < $minimalSeedRange[0] || $seedRange[0] > $minimalSeedRange[1]) {
                        continue;
                    }
                    echo('seed : '.$seed = max($seedRange[0], $minimalSeedRange[0]).PHP_EOL);
                    echo('location : '.$this->searchLocation($seed).PHP_EOL);

                    $output->writeln('<comment>==> Memory usage: '.round(memory_get_peak_usage(true)/1000000, 1).' Mo</comment>');
                    $end = microtime(true);
                    echo($end - $start .' seconds'.PHP_EOL);

                    return Command::SUCCESS;
                }
            }
        }

        return Command::FAILURE;
    }

    protected function getSeedsRange(string $line): array
    {
        $range = [];
        preg_match_all(self::SEEDS_PATTERN, $line, $matches);
        foreach(array_chunk($matches['seeds'], 2) as $seedComposition) {
            list($seedStart, $seedsNumber) = $seedComposition;
            $range[] = [$seedStart, $seedStart + $seedsNumber];
        }

        return $range;
    }

    protected function readMapping(string $line): array
    {
        preg_match(self::MAPPING_PATTERN, $line, $matches);

        return [
            'source' => [(int)$matches['source'], (int)$matches['source'] + (int)$matches['range'] - 1],
            'destination' => [(int)$matches['destination'] , (int)$matches['destination'] + (int)$matches['range'] - 1]
        ];
    }

    protected function getSeedsToCheck(int $min, int $max, int $stepInc = 6): Generator
    {
        if ($stepInc == 0) {
            foreach($this->maps[self::STEPS[$stepInc]] as $map) {
                if ($map['destination'][0] > $max || $map['destination'][1] < $min ) {
                    continue;
                }

                $minGap = 0;
                if ($min > $map['destination'][0]) {
                    $minGap = $min - $map['destination'][0];
                }

                $maxGap = 0;
                if ($max < $map['destination'][1]) {
                    $maxGap = $map['destination'][1] - $max;
                }

                yield [$map['source'][0] + $minGap, $map['source'][1] - $maxGap];
            }

            if ($min > $map['destination'][1]) {
                yield [$min, $max];
            }

            return;
        }

        foreach ($this->maps[self::STEPS[$stepInc]] as $map) {
            if ($map['destination'][0] > $max || $map['destination'][1] < $min ) {
                continue;
            }

            $minGap = 0;
            if ($min > $map['destination'][0]) {
                $minGap = $min - $map['destination'][0];
            }

            $maxGap = 0;
            if ($max < $map['destination'][1]) {
                $maxGap = $map['destination'][1] - $max;
            }

            yield from $this->getSeedsToCheck($map['source'][0] + $minGap, $map['source'][1] - $maxGap, $stepInc - 1);
        }

        if ($min > $map['destination'][1]) {
            yield from $this->getSeedsToCheck($min, $max, $stepInc - 1);
        }
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
