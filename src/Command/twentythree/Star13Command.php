<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star13',
    description: '',
    hidden: false
)]
class Star13Command extends Command
{
    const ASH = '.';
    const ROCK = '#';

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
            ->addArgument('version', InputArgument::REQUIRED, 'version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $patterns = [];

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            $hPatterns = [];
            $vPatterns = [];
            $i = 0;
            $length = 0;
            while (($line = fgets($handle)) !== false) {
                $line = rtrim($line);

                if (empty($line)) {
                    $consolidedHPattern = [];
                    foreach($hPatterns as $hpattern) {
                        $consolidedPattern = 0;
                        foreach ($hpattern as $j => $ground) {
                            $consolidedPattern += $ground * 10 ** ($length - $j - 1);
                        }

                        $consolidedHPattern[] = $consolidedPattern;
                    }

                    $consolidedVPattern = [];
                    foreach($vPatterns as $vpattern) {
                        $consolidedPattern = 0;
                        foreach ($vpattern as $j => $ground) {
                            $consolidedPattern += $ground * 10 ** ($i - $j - 1);
                        }

                        $consolidedVPattern[] = $consolidedPattern;
                    }

                    $patterns[] = ['h' => $consolidedHPattern, 'v' => $consolidedVPattern, 'l' => $length, 'n' => $i];

                    $hPatterns = [];
                    $vPatterns = [];
                    $i = 0;
                    $length = 0;
                    continue;
                }

                $field = array_map(function(string $ground){
                    if ($ground == self::ASH) {
                        return 0;
                    }

                    if ($ground == self::ROCK) {
                        return 1;
                    }
                }, str_split($line));

                $length = strlen($line);
                
                $hPatterns[] = $field;

                foreach ($field as $j => $ground) {
                    $vPatterns[$j][$i] = $ground;
                }
                $i++;
            }

            fclose($handle);
        }

        $consolidedHPattern = [];
        foreach($hPatterns as $hpattern) {
            $consolidedPattern = 0;
            foreach ($hpattern as $j => $ground) {
                $consolidedPattern += $ground * 10 ** ($length - $j - 1);
            }

            $consolidedHPattern[] = $consolidedPattern;
        }

        $consolidedVPattern = [];
        foreach($vPatterns as $vpattern) {
            $consolidedPattern = 0;
            foreach ($vpattern as $j => $ground) {
                $consolidedPattern += $ground * 10 ** ($i - $j - 1);
            }

            $consolidedVPattern[] = $consolidedPattern;
        }

        $patterns[] = ['h' => $consolidedHPattern, 'v' => $consolidedVPattern, 'l' => $length, 'n' => $i];

        $horizontals = 0;
        $verticals = 0;
        foreach ($patterns as $pattern) {
            if ($input->getArgument('version') == 'v1') {
                $horizontal = $this->findReflexion($pattern['v']);
                if (!is_null($horizontal)) {
                    $horizontals += $horizontal;  
                }
                $vertical = $this->findReflexion($pattern['h']);
                if (!is_null($vertical)) {
                    $verticals += $vertical;  
                }
            } elseif ($input->getArgument('version') == 'v2') {
                $horizontal = $this->findHorizontalReflexionWithSmudge($pattern);
                if (!is_null($horizontal)) {
                    $horizontals += $horizontal;  
                }
                $vertical = $this->findVerticalReflexionWithSmudge($pattern);
                if (!is_null($vertical)) {
                    $verticals += $vertical;  
                }
            }
        }

        echo($horizontals + 100 * $verticals);

        return Command::SUCCESS;
    }
    
    protected function findReflexion(array $pattern): ?int
    {
        $number = count($pattern);
        $potentials = [];

        for ($n = 1; $n < $number - 1; $n++) {
            for ($i = 0; $i < $number; $i++) {
                if (!isset($pattern[$n + $i]) || !isset($pattern[$n - $i - 1])) {
                    continue;
                }

                $line1 = $pattern[$n + $i];
                $line2 = $pattern[$n - $i - 1];

                if ($line1 !== $line2) {
                    continue 2;
                }
            }
            $potentials[] = $n;
        }

        if (empty($potentials)) {
            return null;
        }

        return current($potentials);
    }

    protected function findHorizontalReflexionWithSmudge(array $pattern): ?int
    {
        $potentialReflexionPos = array_keys($pattern[0]);
        array_pop($potentialReflexionPos);      

        foreach ($pattern as $line) {
            foreach ($potentialReflexionPos as $keyPotential => $potential) {
                for ($i = 0; $i < count($line) / 2 + 1; $i++) {
                    if (!isset($line[$potential + 1 + $i ]) || !isset($line[$potential - $i ])) {
                        continue;
                    }

                    $line1 = 0;

                    $count = count($pattern[$potential + 1 + $i]);

                    foreach ($pattern[$potential + 1 + $i] as $j => $number) {
                        $line1 += $number * 10 ** ($count - $j);
                    }

                    var_dump($line1);
                    die();

                    if ($line[$potential + 1 + $i ] !== $line[$potential - $i]) {



                        unset($potentialReflexionPos[$keyPotential]);
                    }
                }
            }
        }

        if (empty($potentialReflexionPos)) {
            return null;
        }

        return current($potentialReflexionPos) + 1;
    }

    protected function findVerticalReflexion(array $pattern): ?int
    {
        $potentialReflexionPos = array_keys($pattern);
        array_pop($potentialReflexionPos);

        $countLine = count(current($pattern));

        foreach ($potentialReflexionPos as $keyPotential => $potential) {
            for ($i = 0; $i < $countLine; $i++) {
                if (!isset($pattern[$potential + 1 + $i]) || !isset($pattern[$potential - $i])) {
                    continue;
                }

                $line1 = 0;

                $count = count($pattern[$potential + 1 + $i]);

                foreach ($pattern[$potential + 1 + $i] as $j => $number) {
                    $line1 += $number * 10 ** ($count - $j);
                }

                var_dump($line1);
                die();

                // $number = function (array $pattern) {
                //     $nb = 0;
                //     for ($i = 0; $i < count($pattern); $i ++) {
                //         $line1 += $pattern[$i] * 10 ** (count($pattern) - $i);
                //     }
                // }

                // $line1 = array_walk($pattern, $number);

                // $line1 = array_map($pattern[$potential + 1 + $i];
                // $line2 = $pattern[$potential - $i];

                // if ($line1 !== $line2) {
                //     unset($potentialReflexionPos[$keyPotential]);
                //     break;
                // }
            }
        }

        if (empty($potentialReflexionPos)) {
            return null;
        }

        return current($potentialReflexionPos) + 1;
    }

    protected function findVerticalReflexionWithSmudge(array $pattern): ?int
    {
        $potentialReflexionPos = array_keys($pattern);
        array_pop($potentialReflexionPos);

        $countLine = count(current($pattern));

        foreach ($potentialReflexionPos as $keyPotential => $potential) {
            for ($i = 0; $i < $countLine; $i++) {
                if (!isset($pattern[$potential + 1 + $i]) || !isset($pattern[$potential - $i])) {
                    continue;
                }

                $line1 = $pattern[$potential + 1 + $i];
                $line2 = $pattern[$potential - $i];

                if ($line1 !== $line2) {
                    unset($potentialReflexionPos[$keyPotential]);
                    break;
                }
            }
        }

        if (empty($potentialReflexionPos)) {
            return null;
        }

        return current($potentialReflexionPos) + 1;
    }
}
