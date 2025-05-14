<?php

namespace App\DeckHandler;

/**
 * Class Player.
 *
 * @namespace App\DeckHandler
 */
class Player extends Deck
{
    /**
     * @var array
     * @var bool
     */
    protected $cards = [];
    protected $active = true;

    /**
     * Player constructor.
     */
    public function __construct()
    {
        $this->cards = [];
        $this->active = true;
    }

    /**
     * @param array $cards
     *
     * @return array
     */
    public function addCard($cards)
    {
        foreach ($cards as $card) {
            array_push($this->cards, $card);
        }

        return $this->cards;
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->active;
    }

    /**
     * Take care of if buttons displays or not.
     */
    public function changeStatus()
    {
        if (true == $this->active) {
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
     * @return string
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
     * @return array
     */
    public function getValueOfHand()
    {
        $sum = 0;
        $hasAce = false;
        foreach ($this->cards as $card) {
            if ('A' == $card->getRank()) {
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
