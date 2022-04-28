<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class DrawCard extends AbstractController
{
    /**
     * @Route("/card/draw", name="draw-card")
     */
    public function cardDraw(
        SessionInterface $session
    ): Response {
        $data = [
            'title' => 'DrawCard',
            'draw_card' => $session->get('shuffle')->getcard(),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return $this->render('card/draw.html.twig', $data);
    }

    /**
     * @Route("/card/api/deck/draw", name="api-draw")
     */
    public function cardDrawApi(
        SessionInterface $session
    ): Response {
        $data = [
            'title' => 'DrawCard',
            'draw_card' => $session->get('shuffle')->getcard(),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return new JsonResponse($data);
    }

    /**
     * @Route("/card/draw/{draw_amount}", name="draw-card-multi")
    */
    public function cardDrawMulti(
        int $draw_amount,
        SessionInterface $session
    ): Response {
        $data = [
            'title' => 'DrawCard',
            'deck' => 'deck',
            'draw_card' => $session->get('shuffle')->getcard($draw_amount),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return $this->render('card/draw.html.twig', $data);
    }

    /**
     * @Route("/card/api/deck/draw/{draw_amount}", name="api-draw-multi")
    */
    public function cardDrawMultiApi(
        int $draw_amount,
        SessionInterface $session
    ): Response {
        $data = [
            'title' => 'DrawCard',
            'deck' => 'deck',
            'draw_card' => $session->get('shuffle')->getcard($draw_amount),
            'card_as_string' => $session->get('shuffle')->getAsString(),
        ];
        return new JsonResponse($data);
    }
}
