<?php

namespace App\DeckHandler;

class Game
{
    public function __construct()
    {

    }

    public function highestBelow21($arr)
    {
        $highest = 0;
        foreach ($arr as $num) {
            if ($num < 21 && $num > $highest) {
                $highest = $num;
            }
        }
        return $highest;
    }


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

        return "Error";
    }

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
        return false;
    }
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
