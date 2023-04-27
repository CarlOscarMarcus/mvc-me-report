<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BlackjackControllerTwig extends AbstractController
{
    #[Route("/card/blackjackIntro", name: "blackjackIndex")]
    public function blackjackIndex(): Response
    {
        return $this->render('cards/blackjackIndex.html.twig');
    }

    #[Route("/card/blackjack", name: "blackjack", methods: ['GET'])]
    public function blackjack(SessionInterface $session): Response
    {
        // Force new game
        // $session->set("gameStatus", 'new');


        /*
        1. Players get 2 card each with faceup cards
        2. Deal get 2 card one facedown and one faceup
        3. Players get the choose to hit or stand if stands $this->active = false
        4. All player is done with hit and stand show dealers 2:nd card.
        5. Dealer hits until if reaches above 17
        */
        if ($session->get('gameStatus') == null || $session->get('gameStatus') == 'new') {
            //Create new game
            $session->set("gameStatus", 'active');
            $player = new Player();
            $dealer = new Player();
            $deck = new Deck();
            $deck->shuffle();
            $result = "";

            // Give player two cards
            $player->addCard($deck->deal(2));
            $dealer->addCard($deck->deal(2));
            // Set new values
            $session->set('player', $player);
            $session->set('dealer', $dealer);
            $session->set('deck', $deck);
            $session->set('result', $result);
        } else {
            // Get last rounds values
            $player = $session->get('player');
            $dealer = $session->get('dealer');
            $deck = $session->get('deck');
            $result = $session->get('result');
            ;
        }

        if ($session->get('gameStatus') == "stand") {
            // Dealer draws if player have stand
            // Check if dealer are allowed to draw
            // Needs to be under 17 and under all posiblites of players hand
            if($dealer->getValueOfHand()[0] == 21) {
                $session->set('result', 'Dealer Blackjack!');
                $session->set('gameStatus', 'result');
            }

            if($dealer->getValueOfHand()[0] < 17 &&
                $dealer->getValueOfHand()[0] < $player->getValueOfHand()[1]
            ) {
                $dealer->addCard($deck->deal(1));
            } else {
                $session->set('gameStatus', 'result');
            }
            header("Refresh:0");
        } elseif ($session->get('gameStatus') == "result") {
            // Check if players value on hand.
            // If player have ace use high number if not over 21

            if($dealer->getValueOfHand()[0] > 21) {
                $session->set('result', 'Player wins <br> Dealer busts');
            } elseif($player->getValueOfHand()[0] > 21) {
                $session->set('result', 'Dealer wins <br> Player busts');
            } else {
                if($player->getValueOfHand()[1] < 21) {
                    if($player->getValueOfHand()[1] > $dealer->getValueOfHand()[0]) {
                        $session->set('result', 'Player wins <br>');
                    } elseif($player->getValueOfHand()[1] == $dealer->getValueOfHand()[0]) {
                        $session->set('result', 'Tie <br> Push');
                    } else {
                        $session->set('result', 'Dealer wins <br>');
                    }
                } else {
                    if($player->getValueOfHand()[0] > $dealer->getValueOfHand()[0]) {
                        $session->set('result', 'Player wins <br>');
                    } elseif($player->getValueOfHand()[0] == $dealer->getValueOfHand()[0]) {
                        $session->set('result', 'Tie <br> Push');
                    } else {
                        $session->set('result', 'Dealer wins <br>');
                    }
                }
            }
            $session->set('gameStatus', "gameOver");
        }

        // Count players hand value
        $playerValue = $player->getValueOfHand();
        if($playerValue[0] !== $playerValue[1]) {
            if($playerValue[1] > 21) {
                $value = strval($playerValue[0]);
            } else {
                $value = strval($playerValue[0]) . " | " . strval($playerValue[1]);
            }
        } else {
            $value = strval($playerValue[0]);
        }

        // Set new values
        $session->set('player', $player);
        $session->set('dealer', $dealer);
        $session->set('deck', $deck);

        $data = [
            // Players
            "player" => $value,
            "playerCard" => $player->playerToString(),

            // Dealer
            "dealer" => $dealer->getValueOfHand()[0],
            "dealerCard" => $dealer->playerToString(),

            // Other
            'buttons' => $player->getStatus(),
            'gameStatus' => $session->get('gameStatus'),
            'result' => $session->get('result')
        ];

        return $this->render('cards/blackjack.html.twig', $data);
    }

    #[Route("/card/blackjack/hit", name: "blackjackHit")]
    public function blackjackHit(SessionInterface $session): Response
    {
        // Get values and hit player
        $deck = $session->get('deck');
        $player = $session->get('player');

        if($session->get('gameStatus') == "active") {
            // Player draws
            $player->addCard($deck->deal(1));
            // See if player hits blackjack(21) och busts (>21)
            if($player->getValueOfHand()[0] > 21) {
                $player->changeStatus();
                $session->set('gameStatus', 'result');
            } elseif($player->getValueOfHand()[0] == 21 || $player->getValueOfHand()[1] == 21) {
                $player->changeStatus();
                $session->set('gameStatus', 'gameOver');
                $session->set('result', 'Player wins <br> Player Blackjack!');
            }
            $session->set('player', $player);
        }
        $session->set('deck', $deck);
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/card/blackjack/stand", name: "blackjackStand")]
    public function blackjackStand(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'stand');
        $player = $session->get('player');
        $player->changeStatus();
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/card/blackjack/reset", name: "blackjackReset")]
    public function blackjackReset(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'new');
        return $this->redirectToRoute('blackjack');
    }


}
