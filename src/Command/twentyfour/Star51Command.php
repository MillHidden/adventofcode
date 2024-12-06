<?php

namespace App\Command\twentyfour;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: '24-star51',
    description: '',
    hidden: false
)]
class Star51Command extends Command
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

        $invalidRules = [];
        $data = 'rule';
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);
                if ('' == $line) {
                    $data = 'update';

                    continue;
                }

                if ('rule' == $data) {
                    [$before, $after] = $this->readRule($line);

                    $invalidRules[$after][] = $before;
                }

                if ('update' == $data) {
                    $update = $this->readUpdate($line);

                    if ($this->isValidUpdate($update, $invalidRules)) {
                        $total += $this->getMiddle($update);
                    }
                }
            }

            fclose($handle);
        }

        echo $total;

        return Command::SUCCESS;
    }

    protected function readRule(string $line): array
    {
        return explode('|', $line);
    }

    protected function readUpdate(string $line): array
    {
        return explode(',', $line);
    }

    protected function isValidUpdate(array $update, array $invalidRules): bool
    {
        while (!empty($update)) {
            $page = array_shift($update);
            if (!array_key_exists($page, $invalidRules)) {
                continue;
            }

            if (array_intersect($update, $invalidRules[$page])) {
                return false;
            }
        }

        return true;
    }

    protected function getMiddle(array $update): int
    {
        $count = count($update);
        if ($count % 2 == 0) {
            throw new \ErrorException(implode(' ', $update));
        }

        return $update[floor($count/2)];
    }
}
