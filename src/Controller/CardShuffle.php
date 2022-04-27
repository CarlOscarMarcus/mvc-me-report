<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class CardShuffle extends AbstractController
{
    /**
     * @Route("/card/shuffle", name="dice_shuffle")
     */
    public function card_shuffle(
        SessionInterface $session
    ): Response
    {
        $shuffle = new \App\Card\Shuffle();
        $session->set('shuffle', $shuffle);
        $shuffle->shuffleDeck();


        $data = [
            'title' => 'Shuffle',
            'die_value' => $session->get('shuffle'),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return $this->render('card/shuffle.html.twig', $data);
    }

    /**
     * @Route("/card/api/shuffle", name="api_shuffle")
     */
    public function card_shuffle_api(
        SessionInterface $session
    ): Response
    {
        $shuffle = new \App\Card\Shuffle();
        $session->set('shuffle', $shuffle);
        $shuffle->shuffleDeck();


        $data = [
            'title' => 'Shuffle',
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return new JsonResponse($data);
    }
}
