<?php

namespace App\DeckHandler;

/**
 * Class Card.
 *
 * @namespace App\DeckHandler
 */
class Card
{
    /**
     * @var string
     * @var string
     */
    protected $suit;
    protected $rank;

    /**
     * Card constructor.
     */
    public function __construct($suit, $rank)
    {
        $this->suit = $suit;
        $this->rank = $rank;
    }

    /**
     * @return string
     */
    public function getSuit()
    {
        return $this->suit;
    }

    /**
     * @return string
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * @return string
     */
    public function toString()
    {
        if (in_array($this->suit, ['♥', '♦']) || in_array($this->suit, ['3', '4'])) {
            return '<div class="card red">'.$this->rank.$this->suit.'</div>';
        }

        return '<div class="card black">'.$this->rank.$this->suit.'</div>';
    }

    public function toStringApi()
    {
        return $this->rank.$this->suit.' ';
    }
}
