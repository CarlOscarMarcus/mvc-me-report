<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class index extends AbstractController
{
    /**
     * @Route("/")
     */
    public function om(): Response
    {
        return $this->render('index.twig', []);
    }
}
