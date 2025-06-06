<?php

namespace App\DeckHandler;

class Deck
{
    private array $cards = [];

    public function __construct()
    {
        $this->initializeDeck();
    }

    private function initializeDeck(): void
    {
        $suits = [1, 2, 3, 4];
        $values = ['2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K', 'A'];

        $this->cards = [];

        foreach ($suits as $suit) {
            foreach ($values as $value) {
                $this->cards[] = new Card($suit, $value);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    
    public function shuffleAndReturn(): self
    {
        $this->shuffle();
        return $this;
    }

    public function draw(): ?Card
    {
        return array_pop($this->cards) ?: null;
    }

    public function cardsLeft(): int
    {
        return count($this->cards);
    }

    public function toArray(): array
    {
        return array_map(fn($card) => [$card->getSuit(), $card->getRawValue()], $this->cards);
    }

    // Recreate deck from array of arrays [[suit, value], ...]
    public static function fromArray(array $data): self
    {
        $deck = new self();
        $deck->cards = [];

        foreach ($data as [$suit, $value]) {
            $deck->cards[] = Card::fromArray([$suit, (string)$value[0]]);
        }

        return $deck;
    }

    public function peek(): ?Card
    {
        return $this->cards[count($this->cards) - 1] ?? null;  // Returns the first card or null if empty
    }

    /**
     * Sort the current deck.
     */
    public function sort()
    {
        usort($this->cards, function ($suits, $rank) {
            $suitOrder = [1, 2, 3, 4];
            $aSuitIndex = array_search($suits->getSuitId(), $suitOrder);
            $bSuitIndex = array_search($rank->getSuitId(), $suitOrder);

            if ($aSuitIndex < $bSuitIndex) {
                return -1;
            } elseif ($aSuitIndex > $bSuitIndex) {
                return 1;
            }

            $valueOrder = ['A', '2', '3', '4', '5', '6', '7', '8', '9', '10', 'J', 'Q', 'K'];
            $aValueIndex = array_search($suits->getRawValue(), $valueOrder);
            $bValueIndex = array_search($rank->getRawValue(), $valueOrder);

            if ($aValueIndex < $bValueIndex) {
                return -1;
            } elseif ($aValueIndex > $bValueIndex) {
                return 1;
            }
        });
    }

    /**
     * Reformat deck cards to readable format.
     *
     * @return string $cardStrings
     */
    public function deckToString()
    {
        $cardStrings = [];
        foreach ($this->cards as $card) {
            array_push($cardStrings, $card->getDisplay());
        }

        return implode($cardStrings);
    }

    public function deal(int $count): array
    {
        $cards = [];

        for ($i = 0; $i < $count; $i++) {
            $card = $this->draw();
            if ($card === null) {
                break; // Stop if the deck is empty
            }
            $cards[] = $card;
        }

        return $cards;
    }

}
