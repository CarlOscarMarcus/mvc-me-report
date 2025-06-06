<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BlackjackController extends AbstractController
{
    #[Route('/game', name: 'blackjackIndex')]
    public function blackjackIndex(): Response
    {
        return $this->render('game/blackjackIndex.html.twig');
    }

    #[Route('/game/doc', name: 'blackjackDoc')]
    public function blackjackDoc(): Response
    {
        return $this->render('game/blackjackDoc.html.twig');
    }

    #[Route('/game/blackjack', name: 'blackjack')]
    public function index(SessionInterface $session): Response
    {
        $deck = $this->getDeck($session);
        $player = $this->getPlayer($session);
        $dealer = $this->getDealer($session);

        [$dealerLow, $dealerHigh] = $dealer->getTotals();
        $dealerTotal = ($dealerHigh <= 21) ? $dealerHigh : $dealerLow;

        $activePlayerTurn = $session->get('activePlayerTurn', true); // true = player's turn, false = dealer's turn or game over

        [$playerLow, $playerHigh] = $player->getTotals();
        $playerTotal = ($playerHigh <= 21) ? $playerHigh : $playerLow;

        $allPlayersStayed = $player->hasStayed();

        $result = null;

        // Calculate result only when player has stayed or busted
        if ($allPlayersStayed || !$activePlayerTurn) {
            if ($player->isBust()) {
                $result = 'lost';
            } elseif ($dealer->isBust()) {
                $result = 'won';
            } else {
                if ($playerTotal > $dealerTotal) {
                    $result = 'won';
                } elseif ($playerTotal < $dealerTotal) {
                    $result = 'lost';
                } else {
                    $result = 'push';
                }
            }
        }

        return $this->render('game/blackjack.html.twig', [
            'player' => $player,
            'playerTotalLow' => $playerLow,
            'playerTotalHigh' => $playerHigh <= 21 ? $playerHigh : null,
            'dealer' => $dealer,
            'dealerTotalLow' => $dealerLow,
            'dealerTotalHigh' => $dealerHigh <= 21 ? $dealerHigh : null,
            'deckCount' => $deck->cardsLeft(),
            'nextCard' => $deck->peek(),
            'allPlayersStayed' => $allPlayersStayed,
            'activePlayerTurn' => $activePlayerTurn,
            'result' => $result,
        ]);
    }

    #[Route('game/hit', name: 'blackjack_hit', methods: ['POST'])]
    public function hit(SessionInterface $session): Response
    {
        $player = $this->getPlayer($session);
        $deck = $this->getDeck($session);
        $activePlayerTurn = $session->get('activePlayerTurn', true);

        if ($activePlayerTurn && $deck->cardsLeft() > 0 && !$player->hasStayed() && !$player->isBust() && !$player->hasBlackjack()) {
            $player->addCard($deck->draw());

            if ($player->isBust()) {
                $this->dealerPlay($session, $deck);
                $session->set('activePlayerTurn', false);
            }

            if ($player->hasBlackjack()) {
                $player->stay();
                $this->dealerPlay($session, $deck);
                $session->set('activePlayerTurn', false);
            }
        }

        $this->savePlayer($session, $player);
        $this->saveDeck($session, $deck);

        return $this->redirectToRoute('blackjack');
    }

    #[Route('game/stay', name: 'blackjack_stay', methods: ['POST'])]
    public function stay(SessionInterface $session): Response
    {
        $player = $this->getPlayer($session);
        $activePlayerTurn = $session->get('activePlayerTurn', true);

        if (!$activePlayerTurn) {
            return $this->redirectToRoute('blackjack');
        }

        $player->stay();
        $this->savePlayer($session, $player);

        $deck = $this->getDeck($session);
        $this->dealerPlay($session, $deck);
        $session->set('activePlayerTurn', false);

        return $this->redirectToRoute('blackjack');
    }

    #[Route('game/reset', name: 'blackjack_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): RedirectResponse
    {
        $deck = new Deck();
        $deck->shuffle();

        $player = new Player();
        $player->addCard($deck->draw());
        $player->addCard($deck->draw());

        $dealer = $this->initialiseDealer($deck);

        $session->set('deck', $deck->toArray());
        $this->savePlayer($session, $player);
        $this->saveDealer($session, $dealer);

        $session->set('activePlayerTurn', true);

        return $this->redirectToRoute('blackjack');
    }

    /* ---------- PRIVATE HELPERS ---------- */

    private function dealerPlay(SessionInterface $session, Deck $deck): void
    {
        $dealer = $this->getDealer($session);

        while (!$dealer->isBust() && !$dealer->hasStayed()) {
            [$low, $high] = $dealer->getTotals();
            $total = $high <= 21 ? $high : $low;
            if ($total < 17) {
                $dealer->addCard($deck->draw());
            } else {
                $dealer->stay();
            }
        }

        $this->saveDealer($session, $dealer);
        $this->saveDeck($session, $deck);
    }

    private function initialiseDealer(Deck $deck): Player
    {
        $dealer = new Player();
        $dealer->addCard($deck->draw()); // first card visible
        $dealer->addCard($deck->draw()); // second card hidden
        return $dealer;
    }

    /* session helpers */

    private function getDeck(SessionInterface $session): Deck
    {
        $data = $session->get('deck');
        return $data ? Deck::fromArray($data) : (new Deck())->shuffleAndReturn();
    }

    private function saveDeck(SessionInterface $session, Deck $deck): void
    {
        $session->set('deck', $deck->toArray());
    }

    private function getPlayer(SessionInterface $session): Player
    {
        $data = $session->get('player');
        if (!$data) {
            $player = new Player();
            $session->set('player', $player->toArray());
            return $player;
        }
        return Player::fromArray($data);
    }

    private function savePlayer(SessionInterface $session, Player $player): void
    {
        $session->set('player', $player->toArray());
    }

    private function getDealer(SessionInterface $session): Player
    {
        $data = $session->get('dealer');
        return $data ? Player::fromArray($data) : new Player();
    }

    private function saveDealer(SessionInterface $session, Player $dealer): void
    {
        $session->set('dealer', $dealer->toArray());
    }
}