<?php

namespace Tests\DeckHandler;

use PHPUnit\Framework\TestCase;
use App\DeckHandler\Game;

class GameTest extends TestCase
{
    /**
     *   Look after the closest number to blackjack as possible with current hand value.
     *   @covers \App\DeckHandler\Game::highestBelow21
    */
    public function testHighestBelow21()
    {
        $game = new Game();
        $arr = [20, 31];
        $this->assertEquals(20, $game->highestBelow21($arr));
    }

    /**
     * Look throw the hand values and calculate who won player or dealer
     * @covers \App\DeckHandler\Game::highestBelow21
     * @covers \App\DeckHandler\Game::result
     */
    public function testResult()
    {
        $game = new Game();
        $player = array(10, 11);
        $dealer = array(9, 10);
        $this->assertEquals('Player wins <br>', $game->result($player, $dealer));

        $player = array(10, 8);
        $dealer = array(21, 21);
        $this->assertEquals('Dealer wins <br>', $game->result($player, $dealer));

        $player = array(10, 10);
        $dealer = array(10, 10);
        $this->assertEquals('Tie <br> Push', $game->result($player, $dealer));
    }

    /**
     * Check if a player hand is busted or has blackjack
     * @covers \App\DeckHandler\Game::checkValues
     */
    public function testCheckValues()
    {
        // None has busted or blackjack
        $game = new Game();
        $player = [10, 11];
        $dealer = [9, 14];
        $this->assertFalse($game->checkValues($player, $dealer));

        // Player blackjack
        $player = [10, 21];
        $dealer = [9, 14];
        $this->assertEquals('Player Blackjack!', $game->checkValues($player, $dealer));

        // Dealer blackjack
        $player = [10, 10];
        $dealer = [10, 21];
        $this->assertEquals('Dealer Blackjack!', $game->checkValues($player, $dealer));

        // Player busted
        $player = [22, 22];
        $dealer = [9, 14];
        $this->assertEquals('Dealer wins <br> Player Busts', $game->checkValues($player, $dealer));

        // Dealer busted
        $player = [10, 10];
        $dealer = [22, 22];
        $this->assertEquals('Player wins <br> Dealer Busts', $game->checkValues($player, $dealer));
    }

    /**
     * Return the value that player sees
     * If player has ace and not over 21 with high
     * Then display both values that the hand can have
     * @covers \App\DeckHandler\Game::valueToString
     */
    public function testValueToString()
    {
        // If player has ace and under 21 with high
        $game = new Game();
        $hand = [9, 20];
        $this->assertEquals('9 | 20', $game->valueToString($hand));

        // If player has ace and over 21 with high
        $game = new Game();
        $hand = [11, 22];
        $this->assertEquals('11', $game->valueToString($hand));

        // If player has not ace
        $game = new Game();
        $hand = [11, 11];
        $this->assertEquals('11', $game->valueToString($hand));
    }
}