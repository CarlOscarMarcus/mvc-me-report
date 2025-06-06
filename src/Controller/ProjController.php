<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use App\DeckHandler\Balance;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProjController extends AbstractController
{
    #[Route('/proj', name: 'proj_index')]
    public function projIndex(): Response
    {
        return $this->render('proj/index.html.twig');
    }

    #[Route('/proj/about', name: 'proj_about')]
    public function projAbout(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    #[Route('/proj/game', name: 'blackjack_proj')]
    public function blackjackIndex(SessionInterface $session): Response
    {
        $balance = $this->getBalance($session);
        $deck = $this->getDeck($session);
        $players = $this->getPlayers($session);

        $dealer = $this->getDealer($session);
        [$dealerLow, $dealerHigh] = $dealer->getTotals();
        $dealerTotal = ($dealerHigh <= 21) ? $dealerHigh : $dealerLow;

        $activePlayerIndex = $session->get('activePlayerIndex', 0);

        $allPlayersStayed = count($players) > 0 && array_reduce($players, fn($carry, $p) => $carry && $p->hasStayed(), true);

        $playersWithResults = [];

        foreach ($players as $player) {
            [$totalLow, $totalHigh] = $player->getTotals();
            $playerTotal = ($totalHigh <= 21) ? $totalHigh : $totalLow;

            $result = null;

            if ($allPlayersStayed || $session->get('activePlayerIndex') === null) {
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

            $playersWithResults[] = [
                'player' => $player,
                'totalLow' => $totalLow,
                'totalHigh' => $totalHigh <= 21 ? $totalHigh : null,
                'result' => $result,
            ];
        }

        return $this->render('proj/game.html.twig', [
            'players' => $playersWithResults,
            'dealer' => $dealer,
            'dealerTotalLow' => $dealerLow,
            'dealerTotalHigh' => $dealerHigh <= 21 ? $dealerHigh : null,
            'deckCount' => $deck->cardsLeft(),
            'nextCard' => $deck->peek(),
            'allPlayersStayed' => $allPlayersStayed,
            'activePlayerIndex' => $activePlayerIndex,
            'canAddRemovePlayers' => $activePlayerIndex === null,
            'balanceAmount' => $balance->getBalance(),
            'debtAmount' => $balance->getDebt(),
        ]);
    }

    #[Route('/hit', name: 'blackjack_hit', methods: ['POST'])]
    public function hit(SessionInterface $session): Response
    {
        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $player = $players[$activeIndex];

        if ($deck->cardsLeft() > 0 && !$player->hasStayed() && !$player->isBust() && !$player->hasBlackjack()) {
            $player->addCard($deck->draw());

            if ($player->isBust()) {
                $nextIndex = $this->findNextActivePlayer($players, $activeIndex);

                if ($nextIndex === null) {
                    $this->dealerPlay($session, $deck);
                    $session->set('activePlayerIndex', null);
                } else {
                    $session->set('activePlayerIndex', $nextIndex);
                }
            }

            if ($player->hasBlackjack()) {
                $player->stay();

                $nextIndex = $this->findNextActivePlayer($players, $activeIndex);

                if ($nextIndex === null) {
                    $this->dealerPlay($session, $deck);
                    $session->set('activePlayerIndex', null);
                } else {
                    $session->set('activePlayerIndex', $nextIndex);
                }
            }
        }

        $players[$activeIndex] = $player;
        $this->savePlayers($session, $players);
        $this->saveDeck($session, $deck);

        return $this->redirectToRoute('blackjack_proj');
    }

    #[Route('/stay', name: 'blackjack_stay', methods: ['POST'])]
    public function stay(SessionInterface $session): Response
    {
        $players = $this->getPlayers($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $players[$activeIndex]->stay();

        $nextIndex = $this->findNextActivePlayer($players, $activeIndex);

        if ($nextIndex === null) {
            $deck = $this->getDeck($session);
            $this->dealerPlay($session, $deck);
            $session->set('activePlayerIndex', null);
        } else {
            $session->set('activePlayerIndex', $nextIndex);
        }

        $this->savePlayers($session, $players);

        return $this->redirectToRoute('blackjack_proj');
    }

    #[Route('/reset', name: 'blackjack_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): RedirectResponse
    {
        $deck = new Deck();
        $deck->shuffle();

        $players = $this->getPlayers($session);

        $players = array_slice($players, 0, 3);

        foreach ($players as $player) {
            $player->reset();
            $player->addCard($deck->draw());
            $player->addCard($deck->draw());
        }

        $dealer = $this->initialiseDealer($deck);

        $session->set('deck', $deck->toArray());
        $this->savePlayers($session, $players);
        $this->saveDealer($session, $dealer);
        $session->set('activePlayerIndex', 0);

        return $this->redirectToRoute('blackjack_proj');
    }

    #[Route('/add-player', name: 'blackjack_add_player', methods: ['POST'])]
    public function addPlayer(SessionInterface $session): RedirectResponse
    {
        $activePlayerIndex = $session->get('activePlayerIndex');

        if ($activePlayerIndex !== null) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $players = $this->getPlayers($session);
        if (count($players) < 3) {
            $players[] = new Player();
            $this->savePlayers($session, $players);
        }
        return $this->redirectToRoute('blackjack_proj');
    }

    #[Route('/remove-player', name: 'blackjack_remove_player', methods: ['POST'])]
    public function removePlayer(SessionInterface $session): RedirectResponse
    {
        $activePlayerIndex = $session->get('activePlayerIndex');

        if ($activePlayerIndex !== null) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $players = $this->getPlayers($session);
        if (count($players) > 1) {
            array_pop($players);
            $this->savePlayers($session, $players);
        }
        return $this->redirectToRoute('blackjack_proj');
    }

    #[Route('/double-down', name: 'blackjack_double_down', methods: ['POST'])]
    public function doubleDown(SessionInterface $session): RedirectResponse
    {
        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $player = $players[$activeIndex];

        if (!$player->hasStayed() && !$player->isBust() && !$player->hasDoubledDown()) {
            if ($deck->cardsLeft() > 0) {
                $player->addCard($deck->draw());
                $player->doubleDown();

                $nextIndex = $this->findNextActivePlayer($players, $activeIndex);

                if ($nextIndex === null) {
                    $this->dealerPlay($session, $deck);
                    $session->set('activePlayerIndex', null);
                } else {
                    $session->set('activePlayerIndex', $nextIndex);
                }
            }
        }

        $players[$activeIndex] = $player;
        $this->savePlayers($session, $players);
        $this->saveDeck($session, $deck);

        return $this->redirectToRoute('blackjack_proj');
    }

    // Helper methods

    private function getPlayers(SessionInterface $session): array
    {
        $data = $session->get('players', []);
        return array_map(fn($playerArray) => Player::fromArray($playerArray), $data);
    }

    private function savePlayers(SessionInterface $session, array $players): void
    {
        $data = array_map(fn(Player $player) => $player->toArray(), $players);
        $session->set('players', $data);
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

    private function getDeck(SessionInterface $session): Deck
    {
        $data = $session->get('deck');
        return $data ? Deck::fromArray($data) : new Deck();
    }

    private function saveDeck(SessionInterface $session, Deck $deck): void
    {
        $session->set('deck', $deck->toArray());
    }

    private function getBalance(SessionInterface $session): Balance
    {
        $data = $session->get('balance');
        return $data ? Balance::fromArray($data) : new Balance(); // default balance 10, debt 0
    }

    private function saveBalance(SessionInterface $session, Balance $balance): void
    {
        $session->set('balance', $balance->toArray());
    }

    private function findNextActivePlayer(array $players, int $currentIndex): ?int
    {
        $count = count($players);
        for ($i = $currentIndex + 1; $i < $count; $i++) {
            if (!$players[$i]->hasStayed() && !$players[$i]->isBust()) {
                return $i;
            }
        }
        return null;
    }

    private function dealerPlay(SessionInterface $session, Deck $deck): void
    {
        $dealer = $this->getDealer($session);

        while (true) {
            [$low, $high] = $dealer->getTotals();
            $total = ($high <= 21) ? $high : $low;

            if ($total < 17) {
                if ($deck->cardsLeft() === 0) {
                    break;
                }
                $dealer->addCard($deck->draw());
            } else {
                break;
            }
        }

        $this->saveDealer($session, $dealer);
        $this->saveDeck($session, $deck);
    }

    private function initialiseDealer(Deck $deck): Player
    {
        $dealer = new Player();
        $dealer->addCard($deck->draw());
        $dealer->addCard($deck->draw());
        return $dealer;
    }
}
