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
    public function deal_cards(
        int $draw_amount,
        int $players,
        SessionInterface $session
        ): Response
        {

        $data = [
            'title' => 'Card dealer',
            'players_hand' => $session->get('shuffle')->dealcard($players, $draw_amount),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return $this->render('card/draw.html.twig', $data);
    }

    /**
     * @Route("/card/api/deck/deal/{players}/{draw_amount}", name="api-deal")
    */
    public function deal_cards_api(
        int $draw_amount,
        int $players,
        SessionInterface $session
        ): Response
        {

        $data = [
            'title' => 'Card dealer',
            'players_hand' => $session->get('shuffle')->dealcard($players, $draw_amount),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return new JsonResponse($data);
    }
}
