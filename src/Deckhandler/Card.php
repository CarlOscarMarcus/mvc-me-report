<?php

namespace App\DeckHandler\Card;

class Card
{
    protected $suit;
    protected $rank;

    public function __construct($suit, $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    public function getSuit()
    {
        return $this->suit;
    }

    public function getRank()
    {
        return $this->rank;
    }

    public function toString()
    {
        if (in_array($this->suit, ['♥','♦']) || in_array($this->suit, ['3','4'])) {
            return '<div class="card red">' . $this->rank . $this->suit . '</div>';
        }
        return '<div class="card black">' . $this->rank . $this->suit . '</div>';
    }

    public function toStringApi()
    {
        return $this->rank . $this->suit . ' ';
    }
}
