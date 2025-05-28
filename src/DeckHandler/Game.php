<?php

namespace App\DeckHandler;

/**
 * Class Game.
 *
 * @namespace App\DeckHandler
 */
class Game
{
    /**
     * @param array $arr
     *
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
     *
     * @return string
     */
    public function result($player, $dealer)
    {
        $playerHigh = $this->highestBelow21($player);
        $dealerHigh = $this->highestBelow21($dealer);

        if ($playerHigh > $dealerHigh) {
            return 'Player wins <br>';
        } elseif ($playerHigh < $dealerHigh) {
            return 'Dealer wins <br>';
        } elseif ($playerHigh == $dealerHigh) {
            return 'Tie <br> Push';
        }
    }

    /**
     * @param array $player
     * @param array $dealer
     *
     * @return string
     */
    /**
     * Checks if either player or dealer has a blackjack (21)
     *
     * @param array $player Player's hand values
     * @param array $dealer Dealer's hand values
     *
     * @return string|null Result message if blackjack, null otherwise
     */
    private function checkBlackjack($player, $dealer)
    {
        if (21 == $player[0] || 21 == $player[1]) {
            return 'Player Blackjack!';
        }
        
        if (21 == $dealer[0] || 21 == $dealer[1]) {
            return 'Dealer Blackjack!';
        }
        
        return null;
    }

    /**
     * Checks if either player or dealer has busted (over 21)
     *
     * @param array $player Player's hand values
     * @param array $dealer Dealer's hand values
     *
     * @return string|null Result message if bust, null otherwise
     */
    private function checkBust($player, $dealer)
    {
        if ($player[0] > 21 && $player[1] > 21) {
            return 'Dealer wins <br> Player Busts';
        }
        
        if ($dealer[0] > 21 && $dealer[1] > 21) {
            return 'Player wins <br> Dealer Busts';
        }
        
        return null;
    }

    /**
     * Checks the values of both player and dealer hands to determine the game state
     *
     * @param array $player Player's hand values
     * @param array $dealer Dealer's hand values
     *
     * @return string Result message based on the current game state
     */
    public function checkValues($player, $dealer)
    {
        // Check for blackjack
        $blackjackResult = $this->checkBlackjack($player, $dealer);
        if ($blackjackResult !== null) {
            return $blackjackResult;
        }

        // Check for bust
        $bustResult = $this->checkBust($player, $dealer);
        if ($bustResult !== null) {
            return $bustResult;
        }

        return '';
    }

    /**
     * @param array $hand
     *
     * @return string $value
     */
    public function valueToString($hand)
    {
        $value = 0;
        if ($hand[0] !== $hand[1]) {
            if ($hand[1] > 21) {
                $value = strval($hand[0]);

                return $value;
            }
            $value = strval($hand[0]).' | '.strval($hand[1]);

            return $value;
        }
        $value = strval($hand[0]);

        return $value;
    }
}
