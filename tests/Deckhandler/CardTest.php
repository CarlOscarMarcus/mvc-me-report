<?php

namespace App\Tests\DeckHandler;

use App\DeckHandler\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
    public function testGetSuitAndId(): void
    {
        $card = new Card(1, 'A');
        $this->assertSame(1, $card->getSuitId());
        $this->assertSame('♠', $card->getSuit());

        $card = new Card(4, 'K');
        $this->assertSame(4, $card->getSuitId());
        $this->assertSame('♣', $card->getSuit());
    }

    public function testGetValue(): void
    {
        $ace = new Card(2, 'A');
        $this->assertSame([1, 11], $ace->getValue());

        $king = new Card(3, 'K');
        $this->assertSame([10], $king->getValue());

        $ten = new Card(4, '10');
        $this->assertSame([10], $ten->getValue());

        $two = new Card(1, '2');
        $this->assertSame([2], $two->getValue());
    }

    public function testGetDisplay(): void
    {
        $card = new Card(1, 'Q');
        $this->assertSame('♠Q', $card->getDisplay());

        $card = new Card(2, '10');
        $this->assertSame('♥10', $card->getDisplay());
    }

    public function testGetRawValue(): void
    {
        $card = new Card(3, '7');
        $this->assertSame('7', $card->getRawValue());
    }

    public function testFromArray(): void
    {
        $card = Card::fromArray(['♦', '5']);
        $this->assertInstanceOf(Card::class, $card);
        $this->assertSame('♦5', $card->getDisplay());
    }

    public function testFromString(): void
    {
        $card = Card::fromString('♣J');
        $this->assertInstanceOf(Card::class, $card);
        $this->assertSame('♣J', $card->getDisplay());

        $card = Card::fromString('♥10');
        $this->assertSame('♥10', $card->getDisplay());
    }

    public function testInvalidSuitReturnsQuestionMark(): void
    {
        $card = new Card(9, '3');
        $this->assertSame('?', $card->getSuit());
        $this->assertSame('?3', $card->getDisplay());
    }

    public function testInvalidSuitInFromString(): void
    {
        $card = Card::fromString('X9');
        $this->assertSame('?9', $card->getDisplay());
    }
}
