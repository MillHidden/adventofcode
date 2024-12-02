<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Config\Framework\RateLimiter\LimiterConfig\RateConfig;

#[AsCommand(
    name: 'star19',
    description: '',
    hidden: false
)]
class Star19Command extends Command
{
    const WORKFLOW_PATTERN = '/(?<name>\w+){(?<rules>.*)}/';
    const CONDITION_PATTERN = '/(?<category>\w{1})(?<test>[<>]{1})(?<limit>\d+)/';

    const ACCEPTED = 'A';
    const REFUSED = 'R';

    protected array $workflows = [];

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = 0;

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            while (($line = rtrim(fgets($handle))) !== false && !empty($line)) {
                $this->addWorkflow($line); 
            }

            if ($input->getArgument('version') == 'v1') {
                while (($line = rtrim(fgets($handle))) !== false && !empty($line)) {
                    $partRating = $this->readPartRating($line);

                    if ($this->isAccepted($partRating)) {
                        $total += $partRating['x'] + $partRating['a'] + $partRating['s'] + $partRating['m'];
                    }
                }
            }

            fclose($handle);

            if ($input->getArgument('version') == 'v2') {
                $total = $this->computeValidCombinations(['a' => [1, 4000], 'x' => [1, 4000], 's' => [1, 4000], 'm' => [1, 4000]], 'in');
            }
        }

        echo($total);

        return Command::SUCCESS;
    }

    protected function addWorkflow(string $line)
    {
        if (!preg_match(self::WORKFLOW_PATTERN, $line, $matches)) {
            throw new Exception('Invalid workflow '.$line);
        }

        $name = $matches['name'];

        $rules = explode(',', $matches['rules']);

        $workFlow = [];
        foreach($rules as $rule) {
            if (!str_contains($rule, ':')) {
                $workFlow[] = ['next' => $rule];
                continue;
            }

            list($rawCondition, $next) = explode(':', $rule);

            preg_match(self::CONDITION_PATTERN, $rawCondition, $matches);
            $condition = ['category' => $matches['category'], 'test' => $matches['test'], 'limit' => $matches['limit']];

            $workFlow[] = ['condition' => $condition, 'next' => $next]; 
        }

        $this->workflows[$name] = $workFlow;
    }

    protected function readPartRating(string $line): array
    {
        $partRating = ['a' => 0, 'x' => 0, 'm' => 0, 's' => 0];

        $line = str_replace(['{', '}'], '', $line);

        $parts = explode(',', $line);

        foreach ($parts as $part) {
            list($category, $number) = explode('=', $part);

            $partRating[$category] = $number;
        }
        
        return $partRating;
    }

    protected function isAccepted(array $part): bool
    {
        $next = 'in';
        do {
            $next = $this->getNextWorkflow($part, $next);
        } while (!in_array($next, [self::ACCEPTED, self::REFUSED]));

        return $next == self::ACCEPTED;
    }

    protected function getNextWorkflow(array $part, $workflowName): string
    {
        $workflow = $this->workflows[$workflowName];

        foreach ($workflow as $workflowPart) {
            if (!array_key_exists('condition', $workflowPart)) {
                return $workflowPart['next'];
            }

            $condition = $workflowPart['condition'];

            if ($condition['test'] == '<') {
                if ($part[$condition['category']] < $condition['limit']) {
                    return $workflowPart['next'];
                }
            }

            if ($condition['test'] == '>') {
                if ($part[$condition['category']] > $condition['limit']) {
                    return $workflowPart['next'];
                }
            }
        }

        throw new Exception('part passed out workflow');
    }

    protected function computeValidCombinations(array $rating, string $workflowName): int
    {
        $valids = 0;
        $workflow = $this->workflows[$workflowName];

        foreach ($workflow as $workflowPart) {
            $next = $workflowPart['next'];

            if (!array_key_exists('condition', $workflowPart)) {
                $valids += $this->computeValids($next, $rating);
                continue;
            }

            $test = $workflowPart['condition']['test'];
            $category = $workflowPart['condition']['category'];
            $limit = $workflowPart['condition']['limit'];
            $nextRating = $rating;
            $max = max($rating[$category][1], $limit);

            if ($test == '<') {
                if ($rating[$category][0] >= $limit) {
                    continue;
                }
                
                $min = min($rating[$category][0], $limit - 1);
                $inter = min($rating[$category][1], $limit - 1);
                $nextRating[$category][0] = $min;
                $nextRating[$category][1] = $inter;
                $rating[$category][0] = $inter + 1;
                $rating[$category][1] = $max;
            }

            if ($test == '>') {
                if ($rating[$category][1] <= $limit) {
                    continue;
                }

                $min = min($rating[$category][0], $limit);
                $inter = min($rating[$category][1], $limit);
                $rating[$category][0] = $min;
                $rating[$category][1] = $inter;
                $nextRating[$category][0] = $inter + 1;
                $nextRating[$category][1] = $max;
            }

            $valids += $this->computeValids($next, $nextRating);
        }

        return $valids;
    }

    protected function computeValids(string $next, array $rating): int
    {
        if ($next == self::ACCEPTED) {
            $validsParts = [];
            foreach(['a', 'x', 's', 'm'] as $partCategory) {
                $validsParts[] = $rating[$partCategory][1] - $rating[$partCategory][0] + 1;
            }

            return array_product($validsParts);
        }

        if ($next == self::REFUSED) {
            return 0;
        }

        return $this->computeValidCombinations($rating, $next);
    }
}
