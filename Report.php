<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class Report extends AbstractController
{
    /**
     * @Route("/report")
     */
    public function report(): Response
    {
        return $this->render('report.html.twig', []);
    }
}
