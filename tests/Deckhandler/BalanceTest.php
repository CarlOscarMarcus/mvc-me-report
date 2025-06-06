<?php

namespace App\Tests\DeckHandler;

use PHPUnit\Framework\TestCase;
use App\DeckHandler\Balance;

class BalanceTest extends TestCase
{
    public function testInitialBalanceDefaults()
    {
        $balance = new Balance();
        $this->assertSame(10.0, $balance->getBalance());
        $this->assertSame(0.0, $balance->getDebt());
    }

    public function testInitialBalanceCustomValues()
    {
        $balance = new Balance(50.0, 20.0);
        $this->assertSame(50.0, $balance->getBalance());
        $this->assertSame(20.0, $balance->getDebt());
    }

    public function testSetBalance()
    {
        $balance = new Balance();
        $balance->setBalance(25.5);
        $this->assertSame(25.5, $balance->getBalance());
    }

    public function testAdjustLoanIncreasesBalanceAndDebt()
    {
        $balance = new Balance(10.0, 0.0);
        $balance->adjustLoan(15.0);
        $this->assertSame(25.0, $balance->getBalance());
        $this->assertSame(15.0, $balance->getDebt());
    }

    public function testAdjustLoanWithNegativeAmountReducesDebtAndBalance()
    {
        $balance = new Balance(30.0, 20.0);
        $balance->adjustLoan(-10.0);
        $this->assertSame(20.0, $balance->getBalance());
        $this->assertSame(10.0, $balance->getDebt());
    }

    public function testAdjustLoanCannotPayMoreThanDebt()
    {
        $balance = new Balance(50.0, 5.0);
        $balance->adjustLoan(-20.0);
        $this->assertSame(45.0, $balance->getBalance()); // only 5 paid
        $this->assertSame(0.0, $balance->getDebt());
    }

    public function testAdjustLoanCannotPayMoreThanBalance()
    {
        $balance = new Balance(4.0, 10.0);
        $balance->adjustLoan(-20.0);
        $this->assertSame(0.0, $balance->getBalance()); // paid only 4
        $this->assertSame(6.0, $balance->getDebt());
    }

    public function testAdjustLoanZeroDoesNothing()
    {
        $balance = new Balance(15.0, 10.0);
        $balance->adjustLoan(0.0);
        $this->assertSame(15.0, $balance->getBalance());
        $this->assertSame(10.0, $balance->getDebt());
    }

    public function testToArray()
    {
        $balance = new Balance(25.0, 7.0);
        $array = $balance->toArray();

        $this->assertSame(['balance' => 25.0, 'debt' => 7.0], $array);
    }

    public function testFromArray()
    {
        $data = ['balance' => 40.0, 'debt' => 12.0];
        $balance = Balance::fromArray($data);

        $this->assertSame(40.0, $balance->getBalance());
        $this->assertSame(12.0, $balance->getDebt());
    }

    public function testFromArrayWithDefaults()
    {
        $balance = Balance::fromArray([]);

        $this->assertSame(10.0, $balance->getBalance());
        $this->assertSame(0.0, $balance->getDebt());
    }
}
