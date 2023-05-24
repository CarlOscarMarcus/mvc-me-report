<?php

namespace App\DeckHandler;

/**
 * Class Game
 * @namespace App\DeckHandler
 */
class Game
{
    /**
     * @param array $arr
     * @return int $highest
     */
    public function highestBelow21($arr)
    {
        $highest = 0;
        foreach ($arr as $num) {
            if ($num <= 21 && $num > $highest) {
                $highest = $num;
            }
        }
        return $highest;
    }

    /**
     * @param array $player
     * @param array $dealer
     * @return string
     */
    public function result($player, $dealer)
    {
        $playerHigh = $this->highestBelow21($player);
        $dealerHigh = $this->highestBelow21($dealer);

        if ($playerHigh > $dealerHigh) {
            return 'Player wins <br>';
        } elseif($playerHigh < $dealerHigh) {
            return 'Dealer wins <br>';
        } elseif($playerHigh == $dealerHigh) {
            return 'Tie <br> Push';
        }

        return "false";

    }

    /**
     * @param array $player
     * @param array $dealer
     * @return string
     */
    public function checkValues($player, $dealer)
    {
        if($player[0] == 21 || $player[1] == 21) {
            return "Player Blackjack!";
        } elseif($dealer[0] == 21 || $dealer[1] == 21) {
            return "Dealer Blackjack!";
        }

        if($player[0] > 21 && $player[1] > 21) {
            return "Dealer wins <br> Player Busts";
        } elseif($dealer[0] > 21 && $dealer[1] > 21) {
            return "Player wins <br> Dealer Busts";
        }
        return "false";
    }

    /**
     * @param array $hand
     * @return string $value
     */
    public function valueToString($hand)
    {
        $value = 0;
        if($hand[0] !== $hand[1]) {
            if($hand[1] > 21) {
                $value = strval($hand[0]);
                return $value;
            }
            $value = strval($hand[0]) . " | " . strval($hand[1]);
            return $value;
        }
        $value = strval($hand[0]);
        return $value;
    }
}
