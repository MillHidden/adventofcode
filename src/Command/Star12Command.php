<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star12',
    description: '',
    hidden: false
)]
class Star12Command extends Command
{
    const OPERATIONAL = '.';
    const BROKEN = '#';
    const UNKNOWN = '?';

    protected int $maxLoop;

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getArgument('version') == 'v1') {
            $this->maxLoop = 1;
        }

        if ($input->getArgument('version') == 'v2') {
            $this->maxLoop = 5;
        }

        $total = 0;
        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            
            $test = 1;
            while (($line = fgets($handle)) !== false) {
                echo('test '.$test.PHP_EOL);
                $line = rtrim($line);
                $arrangements = $this->computeArrangement($line, false);
                $total += $arrangements;
                $test++;
            }

            fclose($handle);
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function computeArrangement(string $line, bool $test): int
    {
        list($conditions, $criteria) = explode(' ', $line);
        $baseCriteria = array_map(function(string $brokenContinued) {
            return (int)$brokenContinued;
        }, explode(',', $criteria));
        
        $realCriteria = $baseCriteria;
        $realConditions = $conditions;
        for ($i = 0; $i < $this->maxLoop - 1; $i ++) {
            $realCriteria = array_merge($realCriteria, $baseCriteria);
            $realConditions .= '?'.$conditions;
        }

        $countArrangments = $this->computeConditions($realCriteria, $realConditions, $test);

        return $countArrangments;
    }

    protected function computeConditions(array $remainsCriteria, string $remainsConditions, bool $test): int
    {
        if (empty($remainsConditions) && empty($remainsCriteria)) {
            return 1;
        }

        if (empty($remainsConditions) && !empty($remainsCriteria)) {
            return 0;
        }

        if (empty($remainsCriteria)) {
            if (strpos($remainsConditions, self::BROKEN) == 0 ) {
                return 1;
            }

            return 0;
        }

        $brokenCount = substr_count($remainsConditions, self::BROKEN);
        $unknownCount = substr_count($remainsConditions, self::UNKNOWN);

        // if ($test && $brokenCount == 0 && count($remainsCriteria) == array_sum($remainsCriteria)) {
        //     return $this->calculateArrangements($remainsCriteria, $remainsConditions);
        // }

        if (($brokenCount + $unknownCount) < array_sum($remainsCriteria)) {
            return 0;
        }

        $minimalLength = array_sum($remainsCriteria) + count(array_keys($remainsCriteria)) - 1;

        $i = 0;
        while ($remainsConditions[$i] == self::OPERATIONAL && strlen($remainsConditions) - $i > $minimalLength) {
            $i++;
        }

        if (strlen($remainsConditions) - $i < $minimalLength) {
            return 0;
        }

        $remainsConditions = substr($remainsConditions, $i);

        if ($remainsConditions[0] == self::UNKNOWN) {
            $remainsConditions[0] = self::BROKEN;
            $brokenCaseCount = $this->computeConditions($remainsCriteria, $remainsConditions, $test);

            $operationalCaseCount = $this->computeConditions($remainsCriteria, substr($remainsConditions, 1), $test);            

            return $brokenCaseCount + $operationalCaseCount;
        }

        $brokenContinued = current($remainsCriteria);

        for ($i = 0; $i < $brokenContinued; $i++) {
            $spring = $remainsConditions[$i];

            if ($spring == self::OPERATIONAL) {
                return 0;
            }
        }

        array_shift($remainsCriteria);
        $remainsConditions = substr($remainsConditions, $brokenContinued);

        if (empty($remainsConditions)) {
            if (empty($remainsCriteria)) {
                return 1;
            }
            return 0;
        }

        if ($remainsConditions[0] == self::BROKEN) {
            return 0;
        }

        if ($remainsConditions[0] == self::UNKNOWN) {
            $remainsConditions[0] = self::OPERATIONAL;
        }

        return $this->computeConditions($remainsCriteria, $remainsConditions, $test);
    }
    
    protected function generateArrangements(string $conditions, int $position): array
    {
        if ($position >= strlen($conditions)) {
            return [$conditions];
        }

        switch ($conditions[$position]) {
            case self::BROKEN:
            case self::OPERATIONAL:
                return $this->generateArrangements($conditions, $position + 1);
            case self::UNKNOWN:
                $conditions1 = $conditions2 = $conditions;

                $conditions1[$position] = self::BROKEN;
                $conditions2[$position] = self::OPERATIONAL;
                return array_merge($this->generateArrangements($conditions1, $position + 1), $this->generateArrangements($conditions2, $position + 1));
            default:
                throw new Exception('invalid symbol '. $conditions[$position]);
        }
    }
}
