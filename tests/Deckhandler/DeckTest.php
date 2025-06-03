<?php

namespace Tests\DeckHandler;

use PHPUnit\Framework\TestCase;
use App\DeckHandler\Deck;

class DeckTest extends TestCase
{
    /**
     * Check that all cards are created while new deck is called
     * @covers \App\DeckHandler\Deck::__construct
     * @covers \App\DeckHandler\Deck::countDeck
     * @covers \App\DeckHandler\Card::__construct
     */
    public function testDeckCreation()
    {
        $deck = new Deck();
        $this->assertEquals(52, $deck->countDeck());
    }

    /**
     * Check if deck has been shuffled by comparing deck that is not shuffled
     * @covers \App\DeckHandler\Deck::__construct
     * @covers \App\DeckHandler\Deck::shuffle
     * @covers \App\DeckHandler\Deck::deckToString
     * @covers \App\DeckHandler\Deck::deckToStringApi
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::toString
     * @covers \App\DeckHandler\Card::toStringApi
     */
    public function testDeckShuffle()
    {
        $deck = new Deck();
        $originalDeck = $deck->deckToString();
        $deck->shuffle();
        $shuffledDeck = $deck->deckToString();
        $this->assertNotEquals($originalDeck, $shuffledDeck);

        // Api
        $deck = new Deck();
        $originalDeck = $deck->deckToStringApi();
        $deck->shuffle();
        $shuffledDeck = $deck->deckToStringApi();
        $this->assertNotEquals($originalDeck, $shuffledDeck);
    }

    /**
     * Test that an Exception is thrown when not enough cards are available to deal.
     *
     * @covers \App\DeckHandler\Deck::__construct
     * @covers \App\DeckHandler\Deck::shuffle
     * @covers \App\DeckHandler\Deck::deal
     * @covers \App\DeckHandler\Card::__construct
     */
    public function testDealThrowsExceptionWhenNotEnoughCards()
    {
        $deck = new Deck();
        $deck->shuffle();

        // Deal all cards to empty the deck
        $deck->deal(52);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Not enough cards in deck to draw 1 cards. Renew your deck with /card/shuffle');

        // Try to deal when there are no cards left
        $deck->deal(1);
    }

    /**
     * Test to deal cards to player and cards dissapear after being dealt
     * @covers \App\DeckHandler\Deck::__construct
     * @covers \App\DeckHandler\Deck::deal
     * @covers \App\DeckHandler\Deck::countDeck
     * @covers \App\DeckHandler\Card::__construct
     */
    public function testDeckDeal()
    {
        $deck = new Deck();
        $cards = $deck->deal(5);
        $this->assertEquals(5, count($cards));
        $this->assertEquals(47, $deck->countDeck()); 
    }

    /**
     * Test to sort deck with remenings of deck
     * @covers \App\DeckHandler\Deck::__construct
     * @covers \App\DeckHandler\Deck::shuffle
     * @covers \App\DeckHandler\Deck::deckToString
     * @covers \App\DeckHandler\Deck::sort
     * @covers \App\DeckHandler\Card::__construct
     * @covers \App\DeckHandler\Card::getRank
     * @covers \App\DeckHandler\Card::getSuit
     * @covers \App\DeckHandler\Card::toString
    */
    public function testDeckSort()
    {
        $deck = new Deck();
        $deck->shuffle();
        $unsortedDeck = $deck->deckToString();
        $deck->sort();
        $sortedDeck = $deck->deckToString();
        $this->assertNotEquals($unsortedDeck, $sortedDeck);
    }
}