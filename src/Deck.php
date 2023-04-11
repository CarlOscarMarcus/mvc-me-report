<?php

namespace App\Deck;

class Deck
{
    private $cards;

    public function __construct()
    {
        $this->cards = array();
        $suits = array('♠', "♥", "♦", "♣");
        $cardValues = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");

        // Create the deck
        for ($i = 0; $i < count($suits); $i++) {
            for ($j = 0; $j <= 12; $j++) {
                $card = array("suit" => $suits[$i], "rank" => $cardValues[$j]);
                array_push($this->cards, $card);
            }
        }
    }

    public function shuffle()
    {
        shuffle($this->cards);
    }

    public function deal($numCards = 1)
    {
        $cards = array();
        for ($i = 0; $i < $numCards; $i++) {
            if (count($this->cards) > 0) {
                array_push($cards, array_shift($this->cards));
            } else {
                die("Not enough cards in deck to draw {$numCards} cards. <a href='./shuffle'> Renew your deck </a>");
            }
        }
    }

    public function sort()
    {
        usort($this->cards, function ($a, $b) {
            $suitOrder = array('♠', '♥', '♦', '♣');
            $aSuitIndex = array_search($a['suit'], $suitOrder);
            $bSuitIndex = array_search($b['suit'], $suitOrder);

            if ($aSuitIndex < $bSuitIndex) {
                return -1;
            } elseif ($aSuitIndex > $bSuitIndex) {
                return 1;
            } else {
                $valueOrder = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");
                $aValueIndex = array_search($a['rank'], $valueOrder);
                $bValueIndex = array_search($b['rank'], $valueOrder);

                if ($aValueIndex < $bValueIndex) {
                    return -1;
                } elseif ($aValueIndex > $bValueIndex) {
                    return 1;
                } else {
                    return 0;
                }
            }
        });
    }
    
    public function cardsToString()
        {
            $cardStrings = array();
            foreach ($this->cards as $card) {
                $cardString = $card["rank"] . $card["suit"];
                array_push($cardStrings, $cardString);
            }
            return implode(", ", $cardStrings);
        }

}

// Commands
// Get new deck => $deck = new Deck();
// Shuffle deck => $deck->shuffle();
// Deal cards => $deck->deal(n);
// $deck->cardsToString(); How all cards in a string
