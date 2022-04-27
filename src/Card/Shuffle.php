<?php

namespace App\Card;

class Shuffle extends Card {

    private $deck = [];
    private $newdeck = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function shuffleDeck(){
        $this->deck = parent::getFullDeck();
        shuffle($this->deck);
    }

    public function getCard(int $amount = 1): string
    {
        $newstr = "";
        for ($i = 0; $i < $amount; $i++){
            $str = "";
            $str = $this->deck[0];
            array_shift($this->deck);
            array_shift($this->newdeck);

            $d = [];
            if ($str[0] === "1") {
                array_push($d, '♠');
            }elseif ($str[0] === "2") {
                array_push($d, '♥');
            }elseif ($str[0] === "3") {
                array_push($d, '♦');
            }elseif ($str[0] === "4") {
                array_push($d, '♣');
            }
            array_push($d, $str[2]);
            $newstr .= implode("", $d);
            $newstr .= " ";
        }
        return $newstr;
    }

    public function dealCard(int $players = 2,int $amount = 1): string
    {
        $playerhand = [];

        for ($p = 0; $p < $players; $p++) {
            $player = $p+1;
            $newstr = "Player{$player}: ";
            for ($i = 0; $i < $amount; $i++){
                $str = "";
                $str = $this->deck[0];
                array_shift($this->deck);
                array_shift($this->newdeck);

                $d = [];
                if ($str[0] === "1") {
                    array_push($d, '♠');
                }elseif ($str[0] === "2") {
                    array_push($d, '♥');
                }elseif ($str[0] === "3") {
                    array_push($d, '♦');
                }elseif ($str[0] === "4") {
                    array_push($d, '♣');
                }
                array_push($d, $str[2]);
                $newstr .= implode("", $d);
                $newstr .= " ";
            }
            array_push($playerhand, $newstr);
        }

        return implode("<br> ", $playerhand);
    }

    public function getAsString(): string
    {
        $str = "";
        foreach($this->deck as $d) {

            $d = explode(",", $d);

            if ($d[1] === 1){
                $d[1] = 'A';
            } elseif ($d[1] === 11) {
                $d[1] = 'J';
            } elseif ($d[1] === 12) {
                $d[1] = 'Q';
            }elseif ($d[1] === 13) {
                $d[1] = 'K';
            }

            if ($d[0] === "1") {
                $d[0] = '♠';
            }elseif ($d[0] === "2") {
                $d[0] = '♥';
            }elseif ($d[0] === "3") {
                $d[0] = '♦';
            }elseif ($d[0] === "4") {
                $d[0] = '♣';
            }
            if ($this->newdeck < $this->deck)
            {
                array_push($this->newdeck, "[{$d[0]}{$d[1]}]");
            }
        }
        $count = 1;
        foreach($this->newdeck as $d){
            if ($count === 13) {
                $str .= "{$d} <br>";
                $count = 0;
            } else {
                $str .= "{$d} ";
            }
            $count ++;
        }
        return $str;
    }

}