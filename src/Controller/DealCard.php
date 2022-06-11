<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DealCard extends AbstractController
{
    /**
     * @Route("/card/deck/deal/{players}/{draw_amount}", name="draw-deal")
    */
    public function dealCards(
        int $draw_amount,
        int $players,
        SessionInterface $session
    ): Response {
        if (!is_null($session->get('shuffle'))) {
            $playerhand = $session->get('shuffle')->dealcard($players, $draw_amount);
            $deek = $session->get('shuffle')->getAsString();
        } else {
            $playerhand = "Deck empty... Need to shuffle a new deck to deal cards";
            $deek = "Deck empty";
        }
        $data = [
            'title' => 'Card dealer',
            'draw_card' => $playerhand,
            'card_as_string' => $deek,
        ];
        return $this->render('card/draw.html.twig', $data);
    }

    /**
     * @Route("/card/api/deck/deal/{players}/{draw_amount}", name="api-deal")
    */
    public function dealCardsApi(
        int $draw_amount,
        int $players,
        SessionInterface $session
    ): Response {
        $data = [
            'title' => 'Card dealer',
            'players_hand' => $session->get('shuffle')->dealcard($players, $draw_amount),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return new JsonResponse($data);
    }
}
