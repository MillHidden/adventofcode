<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'star7-2',
    description: '',
    hidden: false
)]
class Star72Command extends Command
{
    const HIGH_CARD = 0;
    const ONE_PAIR = 1;
    const TWO_PAIRS = 2;
    const THREE_OF_KIND = 3;
    const FULL_HOUSE = 4;
    const FOUR_OF_KIND = 5;
    const FIVE_OF_KIND = 6;

    const CARDS = [
        'A' => 13, 'K' => 12, 'Q' => 11, 'T' => 10, '9' => 9, '8' => 8, '7' => 7, '6' => 6, '5' => 5, '4' => 4, '3' => 3, '2' => 2, 'J' => 1
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
        $jokerType = null;

        foreach(array_keys(self::CARDS) as $card) {
            $joker = $card == 'J';
            $matches = [];
            preg_match_all("#(?<hand>$card)#", $hand, $matches);
            if (!empty($matches['hand'])) {
                switch(count($matches['hand'])) {
                    case 1:
                        if (is_null($type) && !$joker) {
                            $type = self::HIGH_CARD;
                        }
                        if (is_null($jokerType) && $joker) {
                            $jokerType = self::HIGH_CARD;
                        }
                        break;
                    case 2:
                        if ($joker) {
                            $jokerType = self::ONE_PAIR;
                            break;
                        }

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
                        if ($joker) {
                            $jokerType = self::THREE_OF_KIND;
                            break;
                        }
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
                        if ($joker) {
                            $jokerType = self::FOUR_OF_KIND;
                            break;
                        }
                        $type = self::FOUR_OF_KIND;
                        break;
                    case 5:
                        $type = self::FIVE_OF_KIND;
                        break;
                }
            }
        }

        if (!is_null($jokerType)) {
            switch ($type) {
                case self::HIGH_CARD:
                    switch ($jokerType) {
                        case self::HIGH_CARD:
                            $type = self::ONE_PAIR;
                            break;
                        case self::ONE_PAIR:
                            $type = self::THREE_OF_KIND;
                            break;
                        case self::TWO_PAIRS: 
                            $type = self::FULL_HOUSE;
                            break;
                        case self::THREE_OF_KIND:
                            $type = self::FOUR_OF_KIND;
                            break;
                        case self::FOUR_OF_KIND:
                            $type = self::FIVE_OF_KIND;
                            break;
                    }
                    break;
                case self::ONE_PAIR:
                    switch ($jokerType) {
                        case self::HIGH_CARD:
                            $type = self::THREE_OF_KIND;
                            break;
                        case self::ONE_PAIR:
                            $type = self::FOUR_OF_KIND;
                            break;
                        case self::TWO_PAIRS: 
                            $type = self::FULL_HOUSE;
                            break;
                        case self::THREE_OF_KIND:
                            $type = self::FIVE_OF_KIND;
                            break;
                    }
                    break;
                case self::TWO_PAIRS:
                    switch ($jokerType) {
                        case self::HIGH_CARD:
                            $type = self::FULL_HOUSE;
                            break;
                    }
                    break;
                case self::THREE_OF_KIND:
                    switch ($jokerType) {
                        case self::HIGH_CARD:
                            $type = self::FOUR_OF_KIND;
                            break;
                        case self::ONE_PAIR:
                            $type = self::FIVE_OF_KIND;
                            break;
                    }
                    break;
                case self::FOUR_OF_KIND:
                    switch ($jokerType) {
                        case self::HIGH_CARD:
                            $type = self::FIVE_OF_KIND;
                            break;
                    }
                    break;
            }
        }

        return ['type' => $type, 'hand' => $hand, 'bet' => (int)$bet];
    }
}
