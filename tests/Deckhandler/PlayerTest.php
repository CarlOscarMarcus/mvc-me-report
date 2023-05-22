<?php

namespace Tests\DeckHandler;

use App\DeckHandler\Player;
use App\DeckHandler\Card;
use PHPUnit\Framework\TestCase;

/**
 * Testing the class player
 */
class PlayerTest extends TestCase
{
    /**
     * Test if player can add cards to its hand.
     * @covers \App\DeckHandler\Player::addCard
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::getCards
     */
    public function testAddCard()
    {
        $player = new Player();
        $card1 = new Card('1', 'A');
        $card2 = new Card('1', '10');
        $player->addCard([$card1, $card2]);
        $this->assertEquals([$card1, $card2], $player->getCards());
    }

    /**
     * Testing if players hand value is correct with dressed cards (J,Q,K) in hand.
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::addCard
     * @covers \App\DeckHandler\Player::getValueOfHand
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::getRank
     */
    public function testPlayerValueWithDressedCards()
    {
        $test = new Player();
        $card1 = new Card('1', 'J');
        $card2 = new Card('2', '2');
        $test->addCard([$card1, $card2]);
        $exp = [12,12];
        $this->assertEquals($exp, $test->getValueOfHand());
    }

    /**
     * Test if players status can get acess with getStatus() method.
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::getStatus
     */
    public function testGetStatus()
    {
        $player = new Player();
        $this->assertTrue($player->getStatus());
    }

    /**
     * Test if players status can be change with changeStatus() method.
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::changeStatus
     * @covers \App\DeckHandler\Player::getStatus
     */
    public function testChangeStatus()
    {
        $player = new Player();
        $player->changeStatus();
        $this->assertFalse($player->getStatus());
        $player->changeStatus();
        $this->assertTrue($player->getStatus());
    }

    /**
     * Test if players hand value is correct
     * @covers \App\DeckHandler\Player::getValueOfHand
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::getRank
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::addCard
     */
    public function testGetValueOfHand()
    {
        // With ace
        $player = new Player();
        $card1 = new Card('1', 'A');
        $card2 = new Card('2', '10');
        $player->addCard([$card1, $card2]);
        $this->assertEquals([11, 21], $player->getValueOfHand());
        $card3 = new Card('4', '9');
        $player->addCard([$card3]);
        $this->assertEquals([20, 30], $player->getValueOfHand());

        //Without ace
        $player = new Player();
        $card1 = new Card('1', '1');
        $card2 = new Card('2', '10');
        $player->addCard([$card1, $card2]);
        $this->assertEquals([11, 11], $player->getValueOfHand());
        $card3 = new Card('4', '9');
        $player->addCard([$card3]);
        $this->assertEquals([20, 20], $player->getValueOfHand());
    }

    /**
     * Test the string that returns the players cards.
     * Includes the html code.
     * @covers \App\DeckHandler\Player::playerToString
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::toString
     * @covers \App\DeckHandler\Deck::cardsToString
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::addCard
     */
    public function testPlayerToString()
    {
        $player = new Player();
        $card1 = new Card('1', 'A');
        $player->addCard([$card1]);
        $this->assertEquals('<div class="card black">A1</div>', $player->playerToString());
    }

    /**
     * Test the string that returns the players cards.
     * Without the html code.
     * @covers \App\DeckHandler\Player::playerToStringApi
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::getRank
     * @covers \App\DeckHandler\Card::getSuit
     * @covers \App\DeckHandler\Deck::cardsToStringApi
     * @covers \App\DeckHandler\Player::__construct
     * @covers \App\DeckHandler\Player::addCard
     */
    public function testPlayerToStringApi()
    {
        $player = new Player();
        $card1 = new Card('1', 'A');
        $card2 = new Card('2', '10');
        $player->addCard([$card1, $card2]);
        $this->assertEquals('A1 102 ', $player->playerToStringApi());
    }

}