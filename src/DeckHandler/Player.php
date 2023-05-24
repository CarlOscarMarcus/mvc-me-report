<?php

namespace App\DeckHandler;

/**
 * Class Player
 * @namespace App\DeckHandler
 */
class Player extends Deck
{
    /**
     * @var array $cards
     * @var bool $active
     */
    protected $cards = array();
    protected $active = true;

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->cards = array();
        $this->active = true;
    }

    /**
     * @param array $cards
     * @return array
     */
    public function addCard($cards)
    {
        foreach($cards as $card) {
            array_push($this->cards, $card);
        }
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->active;
    }

    /**
     * Take care of if buttons displays or not
     */
    public function changeStatus()
    {
        if($this->active == true) {
            $this->active = false;
            return;
        }
        $this->active = true;
    }

    /**
     * @return array
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @return array
     */
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

    /**
     * @var int $sum
     * @var bool $hasAce
     * @return array
     */
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
