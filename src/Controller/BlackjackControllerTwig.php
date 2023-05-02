<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use App\DeckHandler\Game;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BlackjackControllerTwig extends AbstractController
{
    #[Route("/game", name: "blackjackIndex")]
    public function blackjackIndex(): Response
    {
        return $this->render('game/blackjackIndex.html.twig');
    }

    #[Route("/game/doc", name: "blackjackDoc")]
    public function blackjackDoc(): Response
    {
        return $this->render('game/blackjackDoc.html.twig');
    }


    #[Route("/game/blackjack", name: "blackjack", methods: ['GET'])]
    public function blackjack(SessionInterface $session): Response
    {
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
        }
        $game = new Game();

        $player = $session->get('player');
        $dealer = $session->get('dealer');
        $deck = $session->get('deck');
        $result = $session->get('result');

        if($game->checkValues($player->getValueOfHand(), $dealer->getValueOfHand())) {
            $session->set('result', $game->checkValues($player->getValueOfHand(), $dealer->getValueOfHand()));
            $player->changeStatus();
            $session->set('gameStatus', 'gameOver');
        }

        if ($session->get('gameStatus') == "stand") {
            // Dealer draws if player stands
            if($dealer->getValueOfHand()[0] < 17 &&
                $game->highestBelow21($dealer->getValueOfHand()) < $game->highestBelow21($player->getValueOfHand())
            ) {
                $dealer->addCard($deck->deal(1));
            }
            $session->set('gameStatus', 'result');
            header("Refresh:0");
        } elseif ($session->get('gameStatus') == "result") {
            $session->set('result', $game->result($player->getValueOfHand(), $dealer->getValueOfHand()));
            $session->set('gameStatus', "gameOver");
        }

        // Set new values
        $session->set('player', $player);
        $session->set('dealer', $dealer);
        $session->set('deck', $deck);

        $data = [
            // Players
            "player" => $game->valueToString($player->getValueOfHand()),
            "playerCard" => $player->playerToString(),

            // Dealer
            "dealer" => $game->valueToString($dealer->getValueOfHand()),
            "dealerCard" => $dealer->playerToString(),

            // Other
            'buttons' => $player->getStatus(),
            'gameStatus' => $session->get('gameStatus'),
            'result' => $session->get('result')
        ];

        return $this->render('game/blackjack.html.twig', $data);
    }

    #[Route("/game/blackjack/hit", name: "blackjackHit")]
    public function blackjackHit(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $player = $session->get('player');

        if($session->get('gameStatus') == "active") {
            // Player draws
            $player->addCard($deck->deal(1));
            $session->set('player', $player);
        }
        $session->set('deck', $deck);
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/game/blackjack/stand", name: "blackjackStand")]
    public function blackjackStand(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'stand');
        $player = $session->get('player');
        $player->changeStatus();
        $session->set('player', $player);
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/game/blackjack/reset", name: "blackjackReset")]
    public function blackjackReset(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'new');
        return $this->redirectToRoute('blackjack');
    }
}
