<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class QuoteControllerApi{
    #[Route("/api/quote")]
    public function jsonNumber(): Response
    {
        $quote = array(
        "The only way to do great work is to love what you do. - Steve Jobs", 
        "Believe you can and you're halfway there. - Theodore Roosevelt", 
        "Success is not final, failure is not fatal: it is the courage to continue that counts. - Winston Churchill"
        );

        $randomQoute = array_rand($quote);
        
        date_default_timezone_set('Europe/Stockholm');

        $data = [
            'Random Qoute' => $quote[$randomQoute],
            'Todays Date' => date("Y-m-d"),
            'Current Time' => date('H:i:s')
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

}