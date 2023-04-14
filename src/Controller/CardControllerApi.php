<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CardControllerApi
{
    #[Route("/api/quote", name:"api_qoute")]
    public function jsonQoute(): Response
    {
        $quote = array(
        "The only way to do great work is to love what you do. - Steve Jobs",
        "Believe you can and you're halfway there. - Theodore Roosevelt",
        "Success is not final, failure is not fatal: it is the courage to continue that counts. - Winston Churchill"
        );

        $randomQoute = array_rand($quote);

        date_default_timezone_set('Europe/Stockholm');

        $data = [
            'Random Qoute' => $quote[$randomQoute],
            'Todays Date' => date("Y-m-d"),
            'Current Time' => date('H:i:s')
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck", name:"api_deck_get", methods: ['GET'])]
    public function api_deck(SessionInterface $session): Response
    {
        $deck = new Deck();
        $deck->sort();
        $data = [
            'Spader' => '♠',
            'Hjarter' => '♥',
            'Ruter' => '♦',
            'Klover' => '♣',
            'deck' => $deck->deckToStringApi()
        ];

        $response = new JsonResponse($data);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/shuffle", name:"api_deck_shuffle", methods:['POST'])]
    public function api_deck_shuffle(): Response
    {
        $deck = new Deck();
        $deck->shuffle();
        $data = [
            'Spader' => '♠',
            'Hjarter' => '♥',
            'Ruter' => '♦',
            'Klover' => '♣',
            'deck' => $deck->deckToStringApi()
        ];

        $response = new JsonResponse($data);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw", name:"api_deck_draw", methods:['POST'])]
    public function api_deck_draw(): Response
    {
        $deck = new Deck();
        $deck->shuffle();
        $card = $deck->deal();
        $data = [
            'Spader' => '♠',
            'Hjarter' => '♥',
            'Ruter' => '♦',
            'Klover' => '♣',
            'Card' => $deck->cardsToStringApi($card),
            'deck_left' => $deck->countDeck()
        ];

        $response = new JsonResponse($data);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw/{number<\d+>}", name:"api_deck_draw_multi", methods:['POST'])]
    public function api_deck_draw_costum(int $number = 1): Response
    {
        $deck = new Deck();
        $deck->shuffle();
        $card = $deck->deal($number);
        $data = [
            'Spader' => '♠',
            'Hjarter' => '♥',
            'Ruter' => '♦',
            'Klover' => '♣',
            'Cards' => $deck->cardsToStringApi($card),
            'deck_left' => $deck->countDeck()
        ];

        $response = new JsonResponse($data);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/card/draw/{players<\d+>}/{number<\d+>}", name: "api_deck_draw_player", methods: ["POST"])]
    public function cardDrawPlayers(SessionInterface $session, int $players = 1, int $number = 1): Response
    {
        $player_hands = [];
        // $deck = $session->get('deck');
        $deck = new Deck();
        $deck->shuffle();

        for($x = 0; $x < $players; $x++) {
            array_push($player_hands, $deck->deal($number));
        }

        $session->set('deck', $deck);
        $data = [
            'Players' => $deck->playersToStringApi($player_hands),
            'deck_left' => $deck->countDeck()
        ];
        $response = new JsonResponse($data);
        $response->setContent(json_encode($data, JSON_UNESCAPED_UNICODE));
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;

    }




}
