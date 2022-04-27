<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardIndex extends AbstractController
{
    /**
     * @Route("/card", name="card_index")
     */
    public function card_index(): Response
    {
        return $this->render('card/index.html.twig', []);
    }
}
