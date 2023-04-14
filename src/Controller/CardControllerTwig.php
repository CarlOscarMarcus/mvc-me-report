<?php

namespace App\Controller;

use App\Deck\Deck;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardControllerTwig extends AbstractController
{

    #[Route("/card", name: "card")]
    public function card(): Response
    {
        return $this->render('cards/index.html.twig');
    }

    // #[Route("/deck", name: "deck")]
    // public function deck(): Response
    // {
    //     $deck = new Deck();
    //     $deck->sort();
    //     $data = [
    //         'deck' => $deck->cardsToString()
    //     ];

    //     return $this->render('cards/deck.html.twig', $data);
    // }
}