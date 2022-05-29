<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BlackJack extends AbstractController
{
    /**
     * @Route("/game/home", name="gameHome")
     */
    public function gameHome(){
        return $this->render('game/home.html.twig', []);
    }

        /**
     * @Route("/game/doc", name="gameDoc")
     */
    public function gameDoc(){
        return $this->render('game/doc.html.twig', []);
    }


    /**
     * @Route("/game/blackjack", name="blackjack")
    */
    public function deal_cards(
        SessionInterface $session
        ): Response
        {
            // Seasson Player
            $player = $session->get('player_hand') ?? [];
            $player_value = intval($session->get('player_value')) ?? 0;

            // Seasson Bank
            $bank = $session->get('bank_hand') ?? [];
            $bank_value = intval($session->get('bank_value')) ?? 0;

            // Seasson Otherr
            $state = $session->get('state') ?? true;
            $counter = $session->get('counter') ?? array(0, 0);

            $text = $session->get('text') ?? "";

            if ($state){
                if (isset($_POST['action'])) {
                    // If player stay
                    if ($_POST['action'] == "Stay") {
                        $state = false;
                        header("Refresh:0");
                    // If player hit
                    } elseif ($_POST['action'] == "Hit"){
                        $card = $session->get('shuffle')->getcard();
                        $text = "";
                        $card_value = substr($card, 3,1);
                        array_push($player, $card);

                        // Change Suits to its int value
                        if ($card_value == "A"){
                            $card_value = 11;
                        } elseif ($card_value == "J"){
                            $card_value = 10;
                        } elseif ($card_value == "Q"){
                            $card_value = 10;
                        } elseif ($card_value == "K"){
                            $card_value = 10;
                        } elseif ($card_value == "A"){
                            $card_value = 11;
                        } else {
                            $card_value = intval($card_value);
                        }
                        $player_value += $card_value;

                        $aceses = ['♠A ', '♥A ', '♦A ', '♣A '];
                        // Check if acese can change selected value if player bust
                        if ($player_value > 21){
                            if (array_intersect($aceses, $player)) {
                                $key = array_search(array_values(array_intersect($aceses, $player))[0], $player);
                                $player[$key] = substr($player[$key], 0, -1) . '(1)';
                                $player_value -= 10;
                            } else {
                                $text = "Player Bust";
                                $state = false;
                            }
                            // If player hit blackjack
                        } elseif ($player_value == 21) {
                            $text = "Blackjack";
                            $state = false;
                        }
                    }
                }
                // Player stayed or busted
            } else {
                    // Player bust and bank wins
                    if ($player_value > 21) {
                        $text = "Player bust";
                        // Bank hits untill bust or higher then player
                    } elseif ($bank_value < $player_value and $bank_value < 21) {
                        $card = $session->get('shuffle')->getcard();
                        $card_value = substr($card, 3,1);
                        array_push($bank, $card);

                        // Change Suits to its int value
                        if ($card_value == "A"){
                            $card_value = 11;
                        } elseif ($card_value == "J"){
                            $card_value = 10;
                        } elseif ($card_value == "Q"){
                            $card_value = 10;
                        } elseif ($card_value == "K"){
                            $card_value = 10;
                        } elseif ($card_value == "A"){
                            $card_value = 11;
                        } else {
                            $card_value = intval($card_value);
                        }
                        $bank_value += $card_value;


                        $aceses = ['♠A ', '♥A ', '♦A ', '♣A '];
                        // Check if acese can change selected value if bank bust
                        if ($bank_value > 21 and array_intersect($aceses, $bank)){
                                $key = array_search(array_values(array_intersect($aceses, $bank))[0], $bank);
                                $bank[$key] = substr($bank[$key], 0, -1) . '(1)';
                                $bank_value -= 10;
                            }
                        header("Refresh:0");
                        // bank bust player wins
                    } elseif ($bank_value > 21) {
                        $text = "Bank bust";
                        // bank wins by hand
                    } elseif ($bank_value > $player_value) {
                        $text = "Bank wins";
                        // both player get blackjack
                    } elseif ($bank_value = $player_value and $player_value >= 16) {
                        $text = "Tie";
                    }
                    if (isset($_POST['action'])) {
                        if ($_POST['action'] == "NewGame") {
                            $player = [];
                            $player_value = 0;
                            $bank = [];
                            $bank_value = 0;
                            $text = "";
                            $state = true;
                            $_Post['action'] = "";
                            header("Refresh:0");
                        }
                    }
                    $shuffle = new \App\Card\Shuffle();
                    $shuffle->shuffleDeck();
                    $session->set('shuffle', $shuffle);
            }
            // Seasson sets for post
            $session->set('player_hand', $player);
            $session->set('player_value', $player_value);

            $session->set('bank_hand', $bank);
            $session->set('bank_value', $bank_value);


            $session->set('state', $state);
            $session->set('counter', $counter);

            $session->set('text', $text);

            if ($state){
                $stay_button = '<input type="submit" name="action" value="Stay" />';
                $hit_button = '<input type="submit" name="action" value="Hit" />';
                $new_game = null;
            } else {
                $stay_button = null;
                $hit_button = null;
                $new_game = '<input type="submit" name="action" value="NewGame" />';
            }

        $data = [
            'title' => 'Card dealer',
            'player_hand' => implode(" ,", $player),
            'player_value' => $player_value,
            'bank_hand' => implode(" ,", $bank),
            'bank_value' => $bank_value,
            'text' => $text ?? null,
            'stay_button' => $stay_button,
            'hit_button' => $hit_button,
            'new_game' => $new_game,
        ];
        return $this->render('game/blackjack.html.twig', $data);
    }
}