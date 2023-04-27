<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BlackjackControllerTwig extends AbstractController
{
    #[Route("/card/blackjackIntro", name: "blackjackIndex")]
    public function blackjackIndex(): Response
    {
        return $this->render('cards/blackjackIndex.html.twig');
    }

    #[Route("/card/blackjack", name: "blackjack", methods: ['GET'])]
    public function blackjack(SessionInterface $session): Response
    {
        // Force new game
        // $session->set("gameStatus", 'new');


        /*
        1. Players get 2 card each with faceup cards
        2. Deal get 2 card one facedown and one faceup
        3. Players get the choose to hit or stand if stands $this->active = false
        4. All player is done with hit and stand show dealers 2:nd card.
        5. Dealer hits until if reaches above 17
        */
        if ($session->get('gameStatus') == null || $session->get('gameStatus') == 'new') {
            //Create new game
            $session->set("gameStatus", 'active');
            $player = new Player();
            $dealer = new Player();
            $deck = new Deck();
            $deck->shuffle();

            // Give player two cards
            $player->addCard($deck->deal(2));
            // Set new values
            $session->set('player', $player);
            $session->set('dealer', $dealer);
            $session->set('deck', $deck);
        } else {
            // Get last rounds values
            $player = $session->get('player');
            $dealer = $session->get('dealer');
            $deck = $session->get('deck');
        }

        // Count players hand value
        $playerValue = $player->getValueOfHand();
        if(count($playerValue) > 1){
            if($playerValue[1] > 21){
                $value = strval($playerValue[0]);    
            }else{
                $value = strval($playerValue[0]) . " | " . strval($playerValue[1]);
            }
        } else {
            $value = strval($playerValue[0]);
        }

        // Set new values
        $session->set('player', $player);
        $session->set('dealer', $dealer);
        $session->set('deck', $deck);

        $data = [
            // Players
            "player" => $value,
            "playerCard" => $player->playerToString(),
        
            // Dealer
            "dealer" => "", // $deck->cardsToString($session->get('dealer')),

            // Other
            'buttons' => $player->getStatus(),
            'gameStatus' => $session->get('gameStatus')
        ];

        return $this->render('cards/blackjack.html.twig', $data);
    }

    #[Route("/card/blackjack/hit", name: "blackjackHit")]
    public function blackjackHit(SessionInterface $session): Response
    {
        // Get values and hit player
        $deck = $session->get('deck');
        $player = $session->get('player');
        $player->addCard($deck->deal(1));

        if($player->getValueOfHand()[0] > 21){
            $player->changeStatus();
            $session->set('gameStatus', 'gameOver');
        }elseif($player->getValueOfHand()[0] == 21){
            $player->changeStatus();
            $session->set('gameStatus', 'gameOver');
        }
        $session->set('player', $player);
        $session->set('deck', $deck);
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/card/blackjack/stand", name: "blackjackStand")]
    public function blackjackStand(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'new');
        return $this->redirectToRoute('blackjack');
    }

    #[Route("/card/blackjack/reset", name: "blackjackReset")]
    public function blackjackReset(SessionInterface $session): Response
    {
        $session->set("gameStatus", 'new');
        return $this->redirectToRoute('blackjack');
    }


}
