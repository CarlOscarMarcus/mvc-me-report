<?php

namespace App\DeckHandler;

use App\DeckHandler\Deck;

class Player extends Deck
{
    protected $cards = array();
    public function __construct()
    {
        $this->cards = array();
    }

    public function addCard($card)
    {
        array_push($this->cards, $card);
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function playerToString()
    {
        // Bug needs to be fixed
        $temp = $this->cards[0];
        return parent::cardsToString($temp);
    }

    public function playerToStringApi()
    {
        // Bug needs to be fixed
        $temp = $this->cards[0];
        return parent::cardsToStringApi($temp);
    }
}
