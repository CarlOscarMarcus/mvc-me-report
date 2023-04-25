<?php

namespace App\DeckHandler;

use App\DeckHandler\Card\Card;

class Deck
{
    protected $cards;
    public function __construct()
    {
        $this->cards = array();
        $suits = array('♠', "♣", "♥", "♦");
        $cardValues = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");

        // Create the deck
        for ($i = 0; $i < count($suits); $i++) {
            for ($j = 0; $j <= 12; $j++) {
                $card = new Card($suits[$i], $cardValues[$j]);
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
        $temp = array();
        for ($i = 0; $i < $numCards; $i++) {
            if (count($this->cards) > 0) {
                array_push($temp, array_shift($this->cards));
            } else {
                die("Not enough cards in deck to draw {$numCards} cards. <a href='/card/shuffle'> Renew your deck </a>");
            }
        }
        return $temp;
    }

    public function sort()
    {
        usort($this->cards, function ($a, $b) {
            $suitOrder = array('♠', '♣', '♥', '♦');
            $aSuitIndex = array_search($a->getSuit(), $suitOrder);
            $bSuitIndex = array_search($b->getSuit(), $suitOrder);

            if ($aSuitIndex < $bSuitIndex) {
                return -1;
            } elseif ($aSuitIndex > $bSuitIndex) {
                return 1;
            } else {
                $valueOrder = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");
                $aValueIndex = array_search($a->getRank(), $valueOrder);
                $bValueIndex = array_search($b->getRank(), $valueOrder);

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

    public function deckToString()
    {
        $cardStrings = array();
        foreach ($this->cards as $card) {
            array_push($cardStrings, $card->toString());
        }
        return implode($cardStrings);
    }

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
