<?php

namespace App\Card;

class Card
{

    private $deck = [];
    private $newdeck = [];

    private $suits = [
        '1',
        '2',
        '3',
        '4',
    ];

    private $values = [
        'A',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        '10',
        'J',
        'Q',
        'K',
    ];

    protected $value;
    protected $suit;

    public function __construct()
    {
        $this->value = random_int(1, 13);
    }

    public function getFullDeck(): array
    {

        foreach($this->suits as $s) {
            foreach($this->values as $v) {
                array_push($this->deck, "$s,$v");
            }
        }
        return $this->deck;
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
            if ($this->newdeck < $this->deck) {
                array_push($this->newdeck, "[{$d[0]} {$d[1]}]");
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