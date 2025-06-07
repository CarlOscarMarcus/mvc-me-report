<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Player;
use App\DeckHandler\Balance;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for handling project-related routes.
 */
class ProjController extends AbstractController
{
    /**
     * Shows the index page.
     *
     * @Route('/proj', name: 'proj_index')
     *
     * @return Response
     */
    #[Route('/proj', name: 'proj_index')]
    public function projIndex(): Response
    {
        return $this->render('proj/index.html.twig');
    }

     /**
     * Shows the about page.
     *
     * @Route('/proj/about', name: 'proj_about')
     *
     * @return Response
     */
    #[Route('/proj/about', name: 'proj_about')]
    public function projAbout(): Response
    {
        return $this->render('proj/about.html.twig');
    }

    /**
     * This is the main route that host the game.
     *
     * @Route('/proj/game', name: 'proj_blackjack')
     *
     * @return Response
     */
    #[Route('/proj/game', name: 'proj_blackjack')]
    public function blackjackIndex(SessionInterface $session): Response
    {
        $balance = $this->getBalance($session);
        if (!$balance) {
            $balance = new Balance();
        }
        $deck = $this->getDeck($session);
        $players = $this->getPlayers($session);

        $dealer = $this->getDealer($session);
        [$dealerLow, $dealerHigh] = $dealer->getTotals();
        $dealerTotal = ($dealerHigh <= 21) ? $dealerHigh : $dealerLow;

        $activePlayerIndex = $session->get('activePlayerIndex', 0);

        $gameStarted = $session->get('gameStarted', false);
        $allPlayersStayed = $gameStarted && count($players) > 0 && array_reduce($players, fn($carry, $p) => $carry && $p->hasStayed(), true);


        $playersWithResults = [];
        

        foreach ($players as $player) {
            [$totalLow, $totalHigh] = $player->getTotals();
            $playerTotal = ($totalHigh <= 21) ? $totalHigh : $totalLow;
            $wager = $player->getWager();

            $result = null;

            if ($allPlayersStayed || $session->get('activePlayerIndex') === null && $session->get('gameStarted') == true) {
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

            if ($result === 'won') {
                $balance->setBalance($balance->getBalance() + $wager * 2);
            } elseif ($result === 'push') {
                $balance->setBalance($balance->getBalance() + $wager);
            }

            $this->saveBalance($session, $balance);

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
            'gameStarted' => $gameStarted,
        ]);
    }
    /**
     * Allows the diffrent hand to draw a card
     *
     * @Route('/proj/hit', name: 'proj_hit', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/hit', name: 'proj_hit', methods: ['POST'])]
    public function hit(SessionInterface $session): Response
    {

        if (!$session->get('gameStarted', false)) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $player = $players[$activeIndex];

        if ($deck->cardsLeft() > 0 && !$player->hasStayed() && !$player->isBust() && !$player->hasBlackjack()) {
            $player->addCard($deck->draw());
            /** @var \App\DeckHandler\Player $player */
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

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allows to split the hand if condition is meet
     *
     * @Route('/proj/split/{playerIndex}', name: 'proj_split', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/split/{playerIndex}', name: 'proj_split', methods: ['POST'])]
    public function split(SessionInterface $session, int $playerIndex): RedirectResponse
    {

        $balance = $this->getBalance($session);
        if ($balance->getBalance() < 1) {
            $this->addFlash('error', 'Not enough coins to split.');
            return $this->redirectToRoute('blackjack_proj');
        }

        $balance->setBalance($balance->getBalance() - 1);
        $this->saveBalance($session, $balance);


        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);

        if (!isset($players[$playerIndex])) {
            return $this->redirectToRoute('blackjack_proj');
        }

        $player = $players[$playerIndex];

        $hand = $player->getHand();
        if (count($hand) === 2 && $hand[0]->getRawValue() === $hand[1]->getRawValue()) {
            
            // Create new player for split hand
            $newPlayer = new Player();
            $newPlayer->setWager($player->getWager());
            $newPlayer->addCard($hand[1]); // move second card to new hand
            $player->removeCard(1); // remove second card from original

            // Draw one card each for both hands after split
            $player->addCard($deck->draw());
            $newPlayer->addCard($deck->draw());

            // Mark new player as split hand (optional)
            $newPlayer->markAsSplit();

            // Insert new player immediately after original
            array_splice($players, $playerIndex + 1, 0, [$newPlayer]);

            $this->savePlayers($session, $players);
            $this->saveDeck($session, $deck);
        }

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allows the player to stand.
     * Auto stand exists in case of bust or blackjack
     *
     * @Route('/proj/split/{playerIndex}', name: 'proj_split', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/stay', name: 'proj_stay', methods: ['POST'])]
    public function stay(SessionInterface $session): Response
    {

        if (!$session->get('gameStarted', false)) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $players = $this->getPlayers($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('proj_blackjack');
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

        return $this->redirectToRoute('proj_blackjack');
    }
    /**
     * Reset current hand and start a new game.
     *
     * @Route('proj/reset', name: 'proj_reset', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('proj/reset', name: 'proj_reset', methods: ['POST'])]
    public function reset(SessionInterface $session): RedirectResponse
    {
        $deck = new Deck();
        $deck->shuffle();

        $dealer = new Player(); // dealer gets cards only after start
        $players = [];

        $session->set('deck', $deck->toArray());
        $this->savePlayers($session, $players);
        $this->saveDealer($session, $dealer);
        $session->set('activePlayerIndex', null);
        $session->set('gameStarted', false);

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Indicator then the game starts
     *
     * @Route('proj/start-game', name: 'proj_start_game', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('proj/start-game', name: 'proj_start_game', methods: ['POST'])]
    public function startGame(SessionInterface $session): RedirectResponse
    {
        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);
        $balance = $this->getBalance($session);

        // Calculate total wager cost
        $totalCost = 0;
        foreach ($players as $player) {
            $wager = $player->getWager();
            if ($wager <= 0) {
                $this->addFlash('error', 'Each hand must have a wager greater than 0.');
                return $this->redirectToRoute('proj_blackjack');
            }
            $totalCost += $wager;
        }

        // Check if balance is sufficient
        if ($balance->getBalance() < $totalCost) {
            $this->addFlash('error', 'Not enough coins to cover the total wagers.');
            return $this->redirectToRoute('proj_blackjack');
        }

        // Deduct the total cost
        $balance->setBalance($balance->getBalance() - $totalCost);
        $this->saveBalance($session, $balance);

        // Deal cards
        foreach ($players as $player) {
            $player->addCard($deck->draw());
            $player->addCard($deck->draw());
        }

        $dealer = new Player();
        $dealer->addCard($deck->draw());
        $dealer->addCard($deck->draw());

        // Save game state
        $this->savePlayers($session, $players);
        $this->saveDealer($session, $dealer);
        $this->saveDeck($session, $deck);
        $session->set('activePlayerIndex', 0);
        $session->set('gameStarted', true);

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allow the user to add a hand to current game LIMIT of 3
     *
     * @Route('/proj/add-player', name: 'proj_add_player', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/add-player', name: 'proj_add_player', methods: ['POST'])]
    public function addPlayer(SessionInterface $session): RedirectResponse
    {
        $activePlayerIndex = $session->get('activePlayerIndex');

        if ($activePlayerIndex !== null) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $players = $this->getPlayers($session);
        if (count($players) < 3) {
            $players[] = new Player();
            $this->savePlayers($session, $players);
        }
        return $this->redirectToRoute('proj_blackjack');
    }
    /**
     * Allow the user to remove a hand Minimum 1 hand.
     *
     * @Route('/proj/add-player', name: 'proj_add_player', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/remove-player', name: 'proj_remove_player', methods: ['POST'])]
    public function removePlayer(SessionInterface $session): RedirectResponse
    {
        $activePlayerIndex = $session->get('activePlayerIndex');

        if ($activePlayerIndex !== null) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $players = $this->getPlayers($session);
        if (count($players) > 1) {
            array_pop($players);
            $this->savePlayers($session, $players);
        }
        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allow the user to double down.
     * This function autodraw and autostand the hand.
     *
     * @Route('/proj/double-down', name: 'proj_double_down', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/proj/double-down', name: 'proj_double_down', methods: ['POST'])]
    public function doubleDown(SessionInterface $session): RedirectResponse
    {

        if (!$session->get('gameStarted', false)) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $players = $this->getPlayers($session);
        $deck = $this->getDeck($session);
        $activeIndex = $session->get('activePlayerIndex', 0);

        if (!isset($players[$activeIndex])) {
            return $this->redirectToRoute('proj_blackjack');
        }

        $player = $players[$activeIndex];
        $balance = $this->getBalance($session);
        $currentWager = $player->getWager();

        if ($balance->getBalance() < $currentWager) {
            $this->addFlash('error', 'Not enough coins to double down.');
            return $this->redirectToRoute('proj_blackjack');
        }

        if (!$player->hasStayed() && !$player->isBust() && !$player->hasDoubledDown()) {
            if ($deck->cardsLeft() > 0) {
                $player->doubleWager();
                $balance->setBalance($balance->getBalance() - $currentWager);
                $this->saveBalance($session, $balance);

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

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allow the user to refill its balance and gets the player in debt
     *
     * @Route('/loan', name: 'proj_loan', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/loan', name: 'proj_loan', methods: ['POST'])]
    public function loan(SessionInterface $session, Request $request): RedirectResponse
    {
        $amount = (int) $request->request->get('amount', 0);
        if ($amount == 0) {
            $this->addFlash('error', 'Please enter a non-zero amount.');
            return $this->redirectToRoute('blackjack_proj');
        }

        $balance = $this->getBalance($session);
        $currentLoan = $balance->getDebt();

        if ($amount < 0 && abs($amount) > $currentLoan) {
            $this->addFlash('error', 'You cannot repay more than you owe.');
        } else {
            $balance->adjustLoan($amount);
            $this->saveBalance($session, $balance);
        }


        if ($amount > 0) {
            $this->addFlash('success', sprintf('Loan taken: %.2f coins.', $amount));
        } else {
            $this->addFlash('success', sprintf('Loan paid back: %.2f coins.', abs($amount)));
        }

        return $this->redirectToRoute('proj_blackjack');
    }

    /**
     * Allow the user to change the wager on current hand
     *
     * @Route('/update-wager/{playerIndex}', name: 'blackjack_update_wager', methods: ['POST'])
     *
     * @return Response
     */
    #[Route('/update-wager/{playerIndex}', name: 'blackjack_update_wager', methods: ['POST'])]
    public function updateWager(int $playerIndex, Request $request, SessionInterface $session): RedirectResponse
    {
        $wager = (float)$request->request->get('wager');
        $players = $this->getPlayers($session);
        $balance = $this->getBalance($session);

        if (!isset($players[$playerIndex])) {
            $this->addFlash('error', 'Invalid player index.');
            return $this->redirectToRoute('proj_blackjack');
        }

        if ($wager < 0.1) {
            $this->addFlash('error', 'Minimum wager is 0.1 coins.');
            return $this->redirectToRoute('proj_blackjack');
        }

        // Optional: restrict if wager is more than current balance
        if ($wager > $balance->getBalance()) {
            $this->addFlash('error', 'You do not have enough balance for this wager.');
            return $this->redirectToRoute('proj_blackjack');
        }

        $players[$playerIndex]->setWager($wager);
        $this->savePlayers($session, $players);

        $this->addFlash('success', "Wager updated to {$wager} coins for hand " . ($playerIndex + 1));

        return $this->redirectToRoute('proj_blackjack');
    }

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

    private function getBalance(SessionInterface $session): ?Balance
    {
        $balance = $session->get('balance');
        if ($balance instanceof Balance) {
            return $balance;
        }
        return null;
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

}
