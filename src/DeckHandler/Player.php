<?php

namespace App\DeckHandler;

class Player extends Deck
{
    protected $cards = array();
    protected $active = true;

    public function __construct()
    {
        $this->cards = array();
        $this->active = true;
    }

    public function addCard($cards)
    {
        foreach($cards as $card) {
            array_push($this->cards, $card);
        }
    }

    public function getStatus()
    {
        return $this->active;
    }

    // Take care of if buttons on blackjack displays or not
    public function changeStatus()
    {
        if($this->active == true) {
            $this->active = false;
            return;
        }
        $this->active = true;
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function playerToString()
    {
        // Bug needs to be fixed
        $temp = $this->cards;
        return parent::cardsToString($temp);
    }

    public function playerToStringApi()
    {
        // Bug needs to be fixed
        $temp = $this->cards;
        return parent::cardsToStringApi($temp);
    }

    // Blackjack
    public function getValueOfHand()
    {
        $sum = 0;
        $hasAce = false;
        foreach ($this->cards as $card) {
            if ($card->getRank() == 'A') {
                $hasAce = true;
            } elseif (in_array($card->getRank(), ['J', 'Q', 'K'])) {
                $sum += 10;
            } elseif (!in_array($card->getRank(), ['J', 'Q', 'K'])) {
                $sum += intval($card->getRank());
            }
        }
        if ($hasAce) {
            return [$sum + 1, $sum + 11];
        }
        return [$sum, $sum];
    }
}
