<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CardControllerTwig extends AbstractController
{
    #[Route("/card", name: "card")]
    public function card(): Response
    {
        return $this->render('cards/index.html.twig');
    }

    #[Route("/card/deck", name: "cardDeck")]
    public function cardDeck(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $sortedDeck = clone $deck;
        $sortedDeck->sort();
        $data = [
            'deck' => $sortedDeck->deckToString()
        ];

        return $this->render('cards/deck.html.twig', $data);
    }

    #[Route("/card/shuffle", name: "cardShuffle")]
    public function cardShuffle(SessionInterface $session): Response
    {
        $deck = new Deck();
        $deck->shuffle();
        $session->set('deck', $deck);
        $data = [
            'deck' => $deck->deckToString()
        ];

        return $this->render('cards/shuffle.html.twig', $data);
    }

    #[Route("/card/draw", name: "cardDraw")]
    public function cardDraw(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $cards = $deck->deal();
        $session->set('deck', $deck);
        $data = [
            'deck' => $deck->cardsToString($cards),
            'deck_left' => $deck->countDeck()
        ];

        return $this->render('cards/draw.html.twig', $data);
    }

    #[Route("/card/draw/{number<\d+>}", name: "cardDrawCostum")]
    public function cardDrawCostum(SessionInterface $session, int $number = 1): Response
    {
        $deck = $session->get('deck');
        $cards = $deck->deal($number);
        $session->set('deck', $deck);

        $data = [
            'deck' => $deck->cardsToString($cards),
            'deck_left' => $deck->countDeck()
        ];

        return $this->render('cards/draw.html.twig', $data);
    }

    #[Route("/card/draw/{players<\d+>}/{number<\d+>}", name: "cardDrawPlayers")]
    public function cardDrawPlayers(SessionInterface $session, int $players = 1, int $number = 1): Response
    {
        $player_hands = [];
        $deck = $session->get('deck');

        for($x = 0; $x < $players; $x++) {
            array_push($player_hands, $deck->deal($number));
        }

        $session->set('deck', $deck);
        $data = [
            'deck' => $deck->playersToString($player_hands),
            'deck_left' => $deck->countDeck()
        ];

        return $this->render('cards/draw.html.twig', $data);
    }

    #[Route("/api", name: "apiIndex")]
    public function api_index(): Response
    {
        return $this->render('cards/api_index.html.twig');
    }

}
