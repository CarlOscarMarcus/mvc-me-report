<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyControllerTwig extends AbstractController
{
    #[Route("/lucky", name: "lucky")]
    public function lucky(): Response
    {
        $cardSuits = array('♠', "♥", "♦", "♣");
        $cardValues = array("A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K");

        $randomSuit = array_rand($cardSuits);
        $randomValue = array_rand($cardValues);

        $randomCard = $cardValues[$randomValue] . $cardSuits[$randomSuit];

        $data = [
            'card' => $randomCard
        ];

        return $this->render('lucky/lucky.html.twig', $data);
    }
}