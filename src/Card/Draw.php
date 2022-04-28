<?php

namespace App\Card;

class Draw extends Shuffle
{
    public function __construct()
    {
        parent::__construct();
    }

    public function drawCard($deck): array
    {
        $this->drawCard = $deck[0];
        return $this->drawCard;
    }

    public function updateDeck(): array
    {
        $this->deck = array_shift($this->deck);
        return $this->deck;
    }


    public function getAsString(): string
    {
        $str = "";
        foreach ($this->deck as $d) {
            $d = explode(",", $d);

            if ($d[1] === 1) {
                $d[1] = 'A';
            } elseif ($d[1] === 11) {
                $d[1] = 'J';
            } elseif ($d[1] === 12) {
                $d[1] = 'Q';
            } elseif ($d[1] === 13) {
                $d[1] = 'K';
            }

            if ($d[0] === "1") {
                $d[0] = '♠';
            } elseif ($d[0] === "2") {
                $d[0] = '♥';
            } elseif ($d[0] === "3") {
                $d[0] = '♦';
            } elseif ($d[0] === "4") {
                $d[0] = '♣';
            }

            array_push($this->newdeck, "[{$d[0]}{$d[1]}]");
        }
        $count = 1;
        foreach ($this->newdeck as $d) {
            if ($count === 13) {
                $str .= "{$d} <br>";
                $count = 0;
            } else {
                $str .= "{$d} ";
            }
            $count++;
        }
        return $str;
    }
}
