<?php

namespace App\Tests\DeckHandler;

use PHPUnit\Framework\TestCase;
use App\DeckHandler\Card;
use App\DeckHandler\Player;

class PlayerTest extends TestCase
{
    public function testAddCardUpdatesHand()
    {
        $player = new Player();
        $card = new Card(1, '10');
        $player->addCard($card);

        $this->assertCount(1, $player->getHand());
        $this->assertSame($card, $player->getHand()[0]);
    }

    public function testRemoveCardRemovesCorrectCard()
    {
        $player = new Player();
        $card1 = new Card(1, '5');
        $card2 = new Card(2, 'K');
        $player->addCard($card1);
        $player->addCard($card2);

        $player->removeCard(0);
        $this->assertCount(1, $player->getHand());
        $this->assertSame($card2->getDisplay(), $player->getHand()[0]->getDisplay());
    }

    public function testStaySetsHasStayed()
    {
        $player = new Player();
        $this->assertFalse($player->hasStayed());

        $player->stay();
        $this->assertTrue($player->hasStayed());
    }

    public function testResetClearsAllStates()
    {
        $player = new Player();
        $player->addCard(new Card(1, '10'));
        $player->stay();
        $player->doubleDown();
        $player->markAsSplit();
        $player->setWager(5);

        $player->reset();

        $this->assertEmpty($player->getHand());
        $this->assertFalse($player->hasStayed());
        $this->assertFalse($player->hasDoubledDown());
        $this->assertFalse($player->isSplit());
        $this->assertSame(1, $player->getWager());
    }

    public function testTotalsCalculation()
    {
        $player = new Player();
        $player->addCard(new Card(1, 'A'));
        $player->addCard(new Card(2, '9'));

        $totals = $player->getTotals();
        $this->assertSame([10, 20], $totals);
    }

    public function testBlackjackDetection()
    {
        $player = new Player();
        $player->addCard(new Card(1, 'A'));
        $player->addCard(new Card(2, 'K'));

        $this->assertTrue($player->hasBlackjack());
        $this->assertTrue($player->hasStayed());
    }

    public function testBustDetection()
    {
        $player = new Player();
        $player->addCard(new Card(1, 'K'));
        $player->addCard(new Card(2, 'Q'));
        $player->addCard(new Card(3, '5'));

        $this->assertTrue($player->isBust());
    }

    public function testDoubleDown()
    {
        $player = new Player();
        $this->assertFalse($player->hasDoubledDown());

        $player->doubleDown();
        $this->assertTrue($player->hasDoubledDown());
        $this->assertTrue($player->hasStayed());
    }

    public function testWagerManipulation()
    {
        $player = new Player();
        $player->setWager(10);
        $this->assertSame(10, $player->getWager());

        $player->doubleWager();
        $this->assertSame(20, $player->getWager());
    }

    public function testToAndFromArray()
    {
        $player = new Player();
        $player->addCard(new Card(1, '9'));
        $player->stay();
        $player->doubleDown();
        $player->markAsSplit();
        $player->setWager(15);

        $array = $player->toArray();
        $restored = Player::fromArray($array);

        $this->assertEquals($player->getWager(), $restored->getWager());
        $this->assertTrue($restored->hasStayed());
        $this->assertTrue($restored->hasDoubledDown());
        $this->assertTrue($restored->isSplit());
        $this->assertCount(1, $restored->getHand());
        $this->assertEquals('♠9', $restored->getHand()[0]->getDisplay());
    }
}
