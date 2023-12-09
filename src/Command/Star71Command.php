<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star7-1',
    description: '',
    hidden: false
)]
class Star71Command extends Command
{
    const HIGH_CARD = 0;
    const ONE_PAIR = 1;
    const TWO_PAIRS = 2;
    const THREE_OF_KIND = 3;
    const FULL_HOUSE = 4;
    const FOUR_OF_KIND = 5;
    const FIVE_OF_KIND = 6;

    const CARDS = [
        'A' => 14, 'K' => 13, 'Q' => 12, 'J' => 11, 'T' => 10, '9' => 9, '8' => 8, '7' => 7, '6' => 6, '5' => 5, '4' => 4, '3' => 3, '2' => 2
    ];

    protected function configure(): void
    {
        $this
            ->addArgument('input', InputArgument::REQUIRED, 'input')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hands = [];

        $handle = fopen($input->getArgument('input'), "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                
                $hands[] = $this->getHand(rtrim($line));
            }

            fclose($handle);       
        }

        uasort($hands, function (array $hand1, array $hand2) {            
            if ($hand1['type'] < $hand2['type']) {
                return -1;
            }

            if ($hand1['type'] > $hand2['type']) {
                return 1;
            }

            for ($i = 0; $i < 5; $i++) {
                if (self::CARDS[$hand1['hand'][$i]] < self::CARDS[$hand2['hand'][$i]]) {
                    return -1;
                }

                if (self::CARDS[$hand1['hand'][$i]] > self::CARDS[$hand2['hand'][$i]]) {
                    return 1;
                }
            }

            return 0;
        });

        $gain = 0;
        $rank = 1;
        foreach($hands as $hand) {
            var_dump($hand['hand']);
            $gain += $rank * $hand['bet'];
            $rank ++;
        }

        echo($gain);

        return Command::SUCCESS;
    }

    protected function getHand(string $line): array
    {
        list($hand, $bet) = explode(' ', $line);
        $type = null;
        foreach(array_keys(self::CARDS) as $card) {
            $matches = [];
            preg_match_all("#(?<hand>$card)#", $hand, $matches);
            if (!empty($matches['hand'])) {
                switch(count($matches['hand'])) {
                    case 1:
                        if (is_null($type)) {
                            $type = self::HIGH_CARD;
                        }
                        break;
                    case 2:
                        switch($type) {
                            case self::THREE_OF_KIND:
                                $type = self::FULL_HOUSE;
                                break;
                            case self::ONE_PAIR:
                                $type = self::TWO_PAIRS;
                                break;
                            case self::HIGH_CARD:
                            case null:
                                $type = self::ONE_PAIR;
                        }
                        break;
                    case 3:
                        switch($type) {
                            case self::ONE_PAIR:
                                $type = self::FULL_HOUSE;
                                break;
                            case self::HIGH_CARD:
                            case null:
                                $type = self::THREE_OF_KIND;                                
                        }
                        break;
                    case 4:
                        $type = self::FOUR_OF_KIND;
                        break;
                    case 5:
                        $type = self::FIVE_OF_KIND;
                        break;
                }
            }
        }

        return ['type' => $type, 'hand' => $hand, 'bet' => (int)$bet];
    }
}
