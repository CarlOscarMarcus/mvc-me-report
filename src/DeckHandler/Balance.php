<?php
namespace App\DeckHandler;

class Balance
{
    private float $balance;
    private float $debt; // Total loaned amount

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

    public function takeLoan(float $amount): void
    {
        $this->balance += $amount;
        $this->debt += $amount; // increment total debt by loan amount
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
