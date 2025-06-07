<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CardControllerApi
{
    #[Route('/api/quote', name: 'api_quote')]
    public function jsonQuote(): Response
    {
        $quotes = [
            'The only way to do great work is to love what you do. - Steve Jobs',
            "Believe you can and you're halfway there. - Theodore Roosevelt",
            'Success is not final, failure is not fatal: it is the courage to continue that counts. - Winston Churchill',
        ];

        $randomKey = array_rand($quotes);

        date_default_timezone_set('Europe/Stockholm');

        $data = [
            'random_quote' => $quotes[$randomKey],
            'today_date' => date('Y-m-d'),
            'current_time' => date('H:i:s'),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);

        return $response;
    }

    #[Route('/api/deck', name: 'api_deck_get', methods: ['GET'])]
    public function apiDeck(SessionInterface $session): Response
    {
        $deck = $this->getDeck($session);
        $this->saveDeck($session, $deck);

        $sortedDeck = clone $deck;
        $sortedDeck->sort();

        $data = [
            'deck' => $sortedDeck->toArray(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() |
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    #[Route('/api/deck/shuffle', name: 'api_deck_shuffle', methods: ['POST'])]
    public function apiDeckShuffle(SessionInterface $session): Response
    {
        $deck = (new Deck())->shuffleAndReturn();
        $this->saveDeck($session, $deck);

        $data = [
            'deck' => $deck->toArray(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() |
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    #[Route('/api/deck/draw', name: 'api_deck_draw', methods: ['POST'])]
    public function apiDeckDraw(SessionInterface $session): Response
    {
        $deck = $this->getDeck($session);
        $card = $deck->draw();
        $this->saveDeck($session, $deck);

        $data = [
            'card' => $card->getDisplay(),
            'deck_left' => $deck->cardsLeft(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() |
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    #[Route('/api/deck/draw/{number<\d+>}', name: 'api_deck_draw_multi', methods: ['POST'])]
    public function apiDeckDrawCustom(SessionInterface $session, int $number = 1): Response
    {
        $deck = $this->getDeck($session);
        $cards = $deck->deal($number);

        $cardsDisplay = '';
        foreach ($cards as $card) {
            $cardsDisplay .= $card->getDisplay() . ' ';
        }
        $cardsDisplay = trim($cardsDisplay);

        $this->saveDeck($session, $deck);

        $data = [
            'cards' => $cardsDisplay,
            'deck_left' => $deck->cardsLeft(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() |
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    #[Route('/api/card/draw/{players<\d+>}/{number<\d+>}', name: 'api_deck_draw_player', methods: ['POST'])]
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

        $resultString = '';
        foreach ($playerHands as $index => $hand) {
            $resultString .= 'Player ' . ($index + 1) . ': ';
            foreach ($hand as $card) {
                $resultString .= $card->getDisplay() . ' ';
            }
            $resultString = trim($resultString) . ' | ';
        }
        $resultString = rtrim($resultString, ' | ');

        $this->saveDeck($session, $deck);

        $data = [
            'players' => $resultString,
            'deck_left' => $deck->cardsLeft(),
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() |
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $response;
    }

    #[Route('/api/game', name: 'api_blackjack')]
    public function apiBlackjack(SessionInterface $session): Response
    {
        if ($session->has('player') && $session->has('dealer') && $session->has('deck')) {
            $playerData = $session->get('player');
            $dealerData = $session->get('dealer');

            // Rebuild Player objects from arrays
            $player = Player::fromArray($playerData);
            $dealer = Player::fromArray($dealerData);

            $playerValue = $player->getTotals();
            $playerHand = $player->toArray();

            $dealerValue = $dealer->getTotals();
            $dealerHand = $dealer->toArray();

            $result = $session->get('result', 'NA');
            $gameStatus = $session->get('gameStatus', 'NA');

            // Rebuild deck from session to call countDeck()
            $deck = Deck::fromArray($session->get('deck'));
            $deckCount = $deck->cardsLeft();
        } else {
            $playerValue = 'NA';
            $playerHand = 'NA';
            $dealerValue = 'NA';
            $dealerHand = 'NA';
            $result = 'NA';
            $gameStatus = 'NA';
            $deckCount = 'NA';
        }


        $data = [
            'player_value' => $playerValue,
            'player_hand' => $playerHand,
            'dealer_value' => $dealerValue,
            'dealer_hand' => $dealerHand,
            'result' => $result,
            'game_status' => $gameStatus,
            'deck_left' => $deckCount,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return $response;
    }

    /** Session helper: get Deck from session or new shuffled deck */
    private function getDeck(SessionInterface $session): Deck
    {
        $data = $session->get('deck');
        return $data ? Deck::fromArray($data) : (new Deck())->shuffleAndReturn();
    }

    /** Session helper: save Deck array to session */
    private function saveDeck(SessionInterface $session, Deck $deck): void
    {
        $session->set('deck', $deck->toArray());
    }
}
