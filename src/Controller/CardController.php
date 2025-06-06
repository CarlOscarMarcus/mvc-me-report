<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use App\DeckHandler\Card;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CardController extends AbstractController
{
    #[Route('/card', name: 'card')]
    public function card(): Response
    {
        return $this->render('cards/index.html.twig');
    }

    #[Route('/card/deck', name: 'cardDeck')]
    public function cardDeck(SessionInterface $session): Response
    {
        $deck = $this->getDeck($session);
        $this->saveDeck($session, $deck);

        $sortedDeck = clone $deck;
        $sortedDeck->sort();
        $data = [
            'deck' => $sortedDeck->deckToString(),
        ];
        return $this->render('cards/deck.html.twig', $data);
    }

    #[Route('/card/shuffle', name: 'cardShuffle')]
    public function cardShuffle(SessionInterface $session): Response
    {
        $deck = new Deck();
        $deck->shuffle();
        $this->saveDeck($session, $deck);
        $data = [
            'deck' => $deck->deckToString(),
        ];

        return $this->render('cards/shuffle.html.twig', $data);
    }

    #[Route('/card/draw', name: 'cardDraw')]
    public function cardDraw(SessionInterface $session): Response
    {
        $deck = $this->getDeck($session);
        $card = $deck->draw();
        $this->saveDeck($session, $deck);
        $data = [
            'deck' => $card->getDisplay(),
            'deck_left' => $deck->cardsLeft(),
        ];

        return $this->render('cards/draw.html.twig', $data);
    }

    #[Route("/card/draw/{number<\d+>}", name: 'cardDrawCostum')]
    public function cardDrawCostum(SessionInterface $session, int $number = 1): Response
    {
        $player = new Player();
        $deck = $this->getDeck($session);
        $cards = $deck->deal($number);
        $card_string = "";
        foreach($cards as $card) {
            $card_string .= $card->getDisplay();
        }
        $this->saveDeck($session, $deck);

        $data = [
            'deck' => $card_string,
            'deck_left' => $deck->cardsLeft(),
        ];

        return $this->render('cards/draw.html.twig', $data);
    }

    #[Route("/card/draw/{players<\d+>}/{number<\d+>}", name: 'cardDrawPlayers')]
    public function cardDrawPlayers(SessionInterface $session, int $players = 1, int $number = 1): Response
    {
        $deck = $this->getDeck($session);


        $playerHands = [];

        for ($x = 0; $x < $players; $x++) {
            $player = new Player();
            for ($i = 0; $i < $number; $i++) {
                $player->addCard($deck->draw());
            }
            $playerHands[] = $player->getHand();
        }
        $temp = "";
        foreach ($playerHands as $hand) {
            $temp .= 'Player'.strval((array_search($hand, $playerHands) + 1).': ');
            foreach ($hand as $cards) {
                $temp .= $cards->getDisplay();
            }
            $temp .= '<br>';
        }
        $this->saveDeck($session, $deck);

        return $this->render('cards/draw.html.twig', [
            'deck' => $temp,
            'deck_left' => $deck->cardsLeft()
        ]);
    }

    #[Route('/api', name: 'apiIndex')]
    public function apiIndex(): Response
    {
        return $this->render('cards/api_index.html.twig');
    }

    /* session helpers */
    private function getDeck(SessionInterface $session): Deck
    {
        $data = $session->get('deck');
        return $data ? Deck::fromArray($data) : (new Deck())->shuffleAndReturn();
    }

    private function saveDeck(SessionInterface $session, Deck $deck): void
    {
        $session->set('deck', $deck->toArray());
    }
}
