<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CardDeck extends AbstractController
{
    /**
     * @Route("/card/deck", name="dice-deck")
     */
    public function deck(): Response
    {
        $card = new \App\Card\Card();
        $data = [
            'title' => 'Dice',
            'die_value' => $card->getFullDeck(),
            'card_as_string' => $card->getAsString(),
        ];
        return $this->render('card/deck.html.twig', $data);
    }

    /**
     * @Route("/card/api/deck/", name="api-deck")
    */
    public function deckApi(): Response
    {
        $card = new \App\Card\Card();
        $data = [
            'title' => 'Dice',
            'die_value' => $card->getFullDeck(),
            'card_as_string' => $card->getAsString(),
        ];
        return new JsonResponse($data);
    }

    /**
     * @Route("/card/deck2", name="dice-deck2")
     */
    public function deckJoker(): Response
    {
        $card = new \App\Card\CardJoker();
        $data = [
            'title' => 'Dice',
            'die_value' => $card->getFullDeck(),
            'card_as_string' => $card->getAsString(),
        ];
        return $this->render('card/deck.html.twig', $data);
    }
}
