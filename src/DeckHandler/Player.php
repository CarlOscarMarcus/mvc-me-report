<?php

namespace App\DeckHandler;

class Player
{
    private array $hand = [];
    private bool $hasStayed = false;
    private bool $isBust = false;
    private bool $hasBlackjack = false;
    private bool $hasDoubledDown = false;
    private bool $isSplit = false;
    private int $wager = 1;

    public function addCard(Card $card): void
    {
        $this->hand[] = $card;
        $this->updateStatus();
    }

    public function removeCard(int $index): void
    {
        if (isset($this->hand[$index])) {
            array_splice($this->hand, $index, 1);
        }
    }

    public function stay(): void
    {
        $this->hasStayed = true;
    }

    public function reset(): void
    {
        $this->hand = [];
        $this->hasStayed = false;
        $this->isBust = false;
        $this->hasBlackjack = false;
        $this->hasDoubledDown = false;
        $this->isSplit = false;
        $this->wager = 1;
    }

    public function getHand(): array
    {
        return $this->hand;
    }

    public function hasStayed(): bool
    {
        return $this->hasStayed;
    }

    public function isBust(): bool
    {
        return $this->isBust;
    }

    public function hasBlackjack(): bool
    {
        return $this->hasBlackjack;
    }

    public function getTotals(): array
    {
        $low = 0;
        $high = 0;

        foreach ($this->hand as $card) {
            $vals = $card->getValue();
            $low += $vals[0];
            $high += $vals[1] ?? $vals[0];
        }

        return [$low, $high];
    }


    public function markAsSplit(): void {
        $this->isSplit = true;
    }

    public function isSplit(): bool {
        return $this->isSplit;
    }


    public function doubleDown(): void
    {
        $this->hasDoubledDown = true;
        $this->hasStood = true; // This ends the turn after one card
    }

    public function hasDoubledDown(): bool
    {
        return $this->hasDoubledDown;
    }

    private function updateStatus(): void
    {
        [$low, $high] = $this->getTotals();

        if ($low > 21 && $high > 21) {
            $this->isBust = true;
        }

        if (($high === 21 || $low === 21) && !$this->hasStayed) {
            $this->hasBlackjack = true;
            $this->hasStayed = true;
        }
    }

    public function toArray(): array
    {
        return [
            'hand' => array_map(fn($card) => $card->getDisplay(), $this->hand),
            'hasStayed' => $this->hasStayed,
            'isBust' => $this->isBust,
            'hasBlackjack' => $this->hasBlackjack,
            'hasDoubledDown' => $this->hasDoubledDown,
            'isSplit' => $this->isSplit,
            'wager' => $this->wager,
        ];
    }

    public static function fromArray(array $data): self
    {
        $player = new self();
        $player->hand = array_map(fn($cardData) => Card::fromString($cardData), $data['hand']);
        $player->hasStayed = $data['hasStayed'] ?? false;
        $player->isBust = $data['isBust'] ?? false;
        $player->hasBlackjack = $data['hasBlackjack'] ?? false;
        $player->hasDoubledDown = $data['hasDoubledDown'] ?? false;
        $player->isSplit = $data['isSplit'] ?? false;
        $player->wager = $data['wager'] ?? 1;
        return $player;
    }

    public function setWager(int $amount): void
    {
        $this->wager = $amount;
    }

    public function getWager(): int
    {
        return $this->wager;
    }

    public function doubleWager(): void
    {
        $this->wager *= 2;
    }
}
