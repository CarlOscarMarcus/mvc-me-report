<?php

namespace App\Tests\DeckHandler;

use App\DeckHandler\Card;
use App\DeckHandler\Deck;
use PHPUnit\Framework\TestCase;

class DeckTest extends TestCase
{
    public function testDeckInitializesWith52Cards(): void
    {
        $deck = new Deck();
        $this->assertSame(52, $deck->cardsLeft());
    }

    public function testShuffleDoesNotChangeCardCount(): void
    {
        $deck = new Deck();
        $deck->shuffle();
        $this->assertSame(52, $deck->cardsLeft());
    }

    public function testDrawReducesDeckSize(): void
    {
        $deck = new Deck();
        $card = $deck->draw();
        $this->assertInstanceOf(Card::class, $card);
        $this->assertSame(51, $deck->cardsLeft());
    }

    public function testPeekReturnsTopCard(): void
    {
        $deck = new Deck();
        $peek = $deck->peek();
        $draw = $deck->draw();
        $this->assertEquals($peek->getDisplay(), $draw->getDisplay());
    }

    public function testDrawFromEmptyDeckReturnsNull(): void
    {
        $deck = new Deck();
        for ($i = 0; $i < 52; $i++) {
            $deck->draw();
        }
        $this->assertNull($deck->draw());
    }

    public function testDeckToArrayAndFromArrayRestoresDeck(): void
    {
        $originalDeck = new Deck();
        $array = $originalDeck->toArray();

        $restoredDeck = Deck::fromArray($array);
        $this->assertSame($originalDeck->cardsLeft(), $restoredDeck->cardsLeft());

        $originalTop = $originalDeck->peek();
        $restoredTop = $restoredDeck->peek();

        $this->assertSame($originalTop->getDisplay(), $restoredTop->getDisplay());
    }

    public function testDealReturnsRequestedCards(): void
    {
        $deck = new Deck();
        $dealt = $deck->deal(5);

        $this->assertCount(5, $dealt);
        $this->assertSame(47, $deck->cardsLeft());
        foreach ($dealt as $card) {
            $this->assertInstanceOf(Card::class, $card);
        }
    }

    public function testDealReturnsLessIfDeckTooSmall(): void
    {
        $deck = new Deck();
        $deck->deal(52); // Deplete deck
        $remaining = $deck->deal(5); // Should return []

        $this->assertEmpty($remaining);
    }

    public function testDeckToStringIsReadable(): void
    {
        $deck = new Deck();
        $string = $deck->deckToString();
        $this->assertIsString($string);
        $this->assertStringContainsString('♠', $string);
    }

    public function testShuffleAndReturnReturnsSelf(): void
    {
        $deck = new Deck();
        $result = $deck->shuffleAndReturn();
        $this->assertInstanceOf(Deck::class, $result);
    }

    public function testSortSortsCorrectly(): void
    {
        $deck = new Deck();
        $deck->shuffle(); // Disrupt the order
        $deck->sort();
        $sortedDeck = $deck->toArray();

        // Check if first few cards match expected sorted pattern
        $this->assertEquals(['♠', 'A'], $sortedDeck[0]); // ♠A
        $this->assertEquals(['♠', '2'], $sortedDeck[1]); // ♠2
    }
}
