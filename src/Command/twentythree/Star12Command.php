<?php

namespace App\Command\twentythree;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '23-star12',
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
                $arrangements = $this->computeArrangement($line);
                $total += $arrangements;
                $test++;
            }

            fclose($handle);
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function computeArrangement(string $line): int
    {
        list($conditions, $criteria) = explode(' ', $line);
        $baseCriteria = array_map(function(string $brokenContinued) {
            return (int)$brokenContinued;
        }, explode(',', $criteria));
        
        $realCriteria = $baseCriteria;
        $realConditions = $conditions;
        for ($i = 0; $i < $this->maxLoop - 1; $i++) {
            $realCriteria = array_merge($realCriteria, $baseCriteria);
            $realConditions .= '?'.$conditions;
        }

        $countArrangments = $this->computeConditions($realCriteria, $realConditions);

        return $countArrangments;
    }

    protected function computeConditions(array $remainsCriteria, string $remainsConditions): int
    {
        var_dump($remainsCriteria, $remainsConditions);
        if (empty($remainsConditions) && empty($remainsCriteria)) {
            return 1;
        }

        if (empty($remainsConditions) && !empty($remainsCriteria)) {
            return 0;
        }

        if (empty($remainsCriteria)) {
            return (int)(strpos($remainsConditions, self::BROKEN) == 0);
        }

        $operationalCount = substr_count($remainsConditions, self::OPERATIONAL);

        if ((strlen($remainsConditions) - $operationalCount) < array_sum($remainsCriteria)) {
            return 0;
        }

        //$minimalLength = array_sum($remainsCriteria) + count(array_keys($remainsCriteria)) - 1;

        $i = 0;
        while ($remainsConditions[$i] == self::OPERATIONAL && strlen($remainsConditions) - $i > $minimalLength) {
            $i++;
        }

        // if (strlen($remainsConditions) - $i < $minimalLength) {
        //     return 0;
        // }

        $remainsConditions = substr($remainsConditions, $i);

        $contiguousBroken = 0;
        $operational = false;
        $criteria = $remainsCriteria[0];
        $i = 0;
        while (!$operational && $i < strlen($remainsConditions) && $criteria > 0) {
            if (self::OPERATIONAL == $remainsConditions[$i]) {
                $operational = true;
                continue;
            }

            if (self::BROKEN == $remainsConditions[$i]) {
                $criteria--;
                continue;
            }

            $conditionsCountIfOperational = $this->computeConditions($remainsCriteria, substr($remainsConditions, 1));

            

            $i++;
            $contiguousBroken++;
        }
        var_dump($remainsCriteria, $remainsConditions);
        die();



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

        var_dump($remainsCriteria, $remainsConditions);
        die();

        return $this->computeConditions($remainsCriteria, $remainsConditions, $test);
    }
    
    // protected function generateArrangements(string $conditions, int $position): array
    // {
    //     if ($position >= strlen($conditions)) {
    //         return [$conditions];
    //     }

    //     switch ($conditions[$position]) {
    //         case self::BROKEN:
    //         case self::OPERATIONAL:
    //             return $this->generateArrangements($conditions, $position + 1);
    //         case self::UNKNOWN:
    //             $conditions1 = $conditions2 = $conditions;

    //             $conditions1[$position] = self::BROKEN;
    //             $conditions2[$position] = self::OPERATIONAL;
    //             return array_merge($this->generateArrangements($conditions1, $position + 1), $this->generateArrangements($conditions2, $position + 1));
    //         default:
    //             throw new Exception('invalid symbol '. $conditions[$position]);
    //     }
    // }
}
