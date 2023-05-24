<?php

namespace App\DeckHandler;

use Exception;

/**
 * Class Deck
 * @namespace App\DeckHandler
 */
class Deck
{
    /**
     * Create a new deck with all three suits and 14 values
     * @var array $cards
     * @var array $suits
     */
    protected $cards;
    public function __construct()
    {
        $this->cards = array();
        $suits = array('♠', "♣", "♥", "♦");
        $cardValues = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");

        // Create the deck
        $lenght = count($suits);
        for ($i = 0; $i < $lenght; $i++) {
            for ($j = 0; $j <= 12; $j++) {
                $card = new Card($suits[$i], $cardValues[$j]);
                array_push($this->cards, $card);
            }
        }
    }

    /**
     * Shuffle the deck
     * @return array $cards
     */
    public function shuffle()
    {
        shuffle($this->cards);
    }

    /**
     * Deal a hand amout of cards as argument
     * @param int $numCards
     * @var array $temp
     * @return array $temp
     */
    public function deal($numCards = 1)
    {
        $temp = array();

        if (count($this->cards) < $numCards) {
            throw new Exception("Not enough cards in deck to draw {$numCards} cards. Renew your deck with /card/shuffle");
        }

        for ($i = 0; $i < $numCards; $i++) {
            array_push($temp, array_shift($this->cards));
        }
        return $temp;
    }


    /**
     * Sort the current deck
     * @var array $suitOrder
     * @var Card $aSuitIndex
     * @var Card $bSuitIndex
     * @var array $valueOrder
     */
    public function sort()
    {
        usort($this->cards, function ($suits, $rank) {
            $suitOrder = array('♠', '♣', '♥', '♦');
            $aSuitIndex = array_search($suits->getSuit(), $suitOrder);
            $bSuitIndex = array_search($rank->getSuit(), $suitOrder);

            if ($aSuitIndex < $bSuitIndex) {
                return -1;
            } elseif ($aSuitIndex > $bSuitIndex) {
                return 1;
            }

            $valueOrder = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");
            $aValueIndex = array_search($suits->getRank(), $valueOrder);
            $bValueIndex = array_search($rank->getRank(), $valueOrder);

            if ($aValueIndex < $bValueIndex) {
                return -1;
            } elseif ($aValueIndex > $bValueIndex) {
                return 1;
            }
        });
    }

    /**
     * Reformat deck cards to readable format
     * @var array $cardStrings
     * @return array $cardStrings
     */
    public function deckToString()
    {
        $cardStrings = array();
        foreach ($this->cards as $card) {
            array_push($cardStrings, $card->toString());
        }
        return implode($cardStrings);
    }

    /**
     * Reformat player hand cards to readable format
     * @param array $cards
     * @var array $cardStrings
     * @var Card $cards
     * @return string $cardStrings
     */
    public function cardsToString($cards)
    {
        $cardStrings = array();
        foreach ($cards as $card) {
            array_push($cardStrings, $card->toString());
        }
        return implode("", $cardStrings);
    }

    // API
    public function deckToStringApi()
    {
        $cardStrings = array();
        foreach ($this->cards as $card) {
            array_push($cardStrings, $card->toStringApi());
        }
        return implode($cardStrings);
    }

    public function cardsToStringApi($cards)
    {
        $cardStrings = array();
        foreach ($cards as $card) {
            $cardString = $card->getRank(). $card->getSuit() . ' ';
            array_push($cardStrings, $cardString);
        }
        return implode("", $cardStrings);
    }


    public function countDeck()
    {
        return count($this->cards);
    }
}
