<?php
namespace App\DeckHandler;

class Balance
{
    private float $balance;
    private float $debt;

    public function __construct(float $initialBalance = 10, float $debt = 0)
    {
        $this->balance = $initialBalance;
        $this->debt = $debt;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getDebt(): float
    {
        return $this->debt;
    }

    public function adjustLoan(float $amount): void
    {
        if ($amount > 0) {
            // Taking loan
            $this->balance += $amount;
            $this->debt += $amount;
        } elseif ($amount < 0) {
            // Paying back loan
            $payback = abs($amount);

            // Can't pay back more than debt or more than balance
            $payback = min($payback, $this->debt, $this->balance);

            $this->balance -= $payback;
            $this->debt -= $payback;
        }
        // if amount == 0, do nothing
    }

    public function toArray(): array
    {
        return [
            'balance' => $this->balance,
            'debt' => $this->debt,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['balance'] ?? 10,
            $data['debt'] ?? 0
        );
    }
}
