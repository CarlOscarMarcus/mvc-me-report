<?php

namespace App\Controller;

use App\DeckHandler\Deck;
use App\DeckHandler\Game;
use App\DeckHandler\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/game/blackjack', name: 'blackjack', methods: ['GET'])]
    public function blackjack(SessionInterface $session): Response
    {
        $gameStatus = $session->get('gameStatus');

        if ($gameStatus === null || $gameStatus === 'new') {
            $this->startNewGame($session);
        }

        $player = $session->get('player');
        $dealer = $session->get('dealer');
        $deck = $session->get('deck');
        $game = new Game();

        // Check immediate win/loss
        $result = $game->checkValues($player->getValueOfHand(), $dealer->getValueOfHand());
        if ($result) {
            $session->set('result', $result);
            $player->changeStatus();
            $session->set('gameStatus', 'gameOver');
        }

        // Dealer's turn if player stands
        if ($gameStatus === 'stand') {
            $this->handleDealerTurn($game, $dealer, $player, $deck, $session);
            return $this->redirectToRoute('blackjack'); // Better than header('Refresh:0')
        }

        // Final result calculation
        if ($gameStatus === 'result') {
            $session->set('result', $game->result($player->getValueOfHand(), $dealer->getValueOfHand()));
            $session->set('gameStatus', 'gameOver');
        }

        // Save game state
        $this->saveGameState($session, $player, $dealer, $deck);

        // Render view
        return $this->render('game/blackjack.html.twig', $this->buildViewData($game, $session, $player, $dealer));
    }

    private function startNewGame(SessionInterface $session): void
    {
        $player = new Player();
        $dealer = new Player();
        $deck = new Deck();
        $deck->shuffle();

        $player->addCard($deck->deal(2));
        $dealer->addCard($deck->deal(2));

        $session->set('gameStatus', 'active');
        $session->set('player', $player);
        $session->set('dealer', $dealer);
        $session->set('deck', $deck);
        $session->set('result', '');
    }

    private function handleDealerTurn(Game $game, Player $dealer, Player $player, Deck $deck, SessionInterface $session): void
    {
        while (
            $dealer->getValueOfHand()[0] < 17 &&
            $game->highestBelow21($dealer->getValueOfHand()) < $game->highestBelow21($player->getValueOfHand())
        ) {
            $dealer->addCard($deck->deal(1));
        }

        $session->set('gameStatus', 'result');
    }

    private function saveGameState(SessionInterface $session, Player $player, Player $dealer, Deck $deck): void
    {
        $session->set('player', $player);
        $session->set('dealer', $dealer);
        $session->set('deck', $deck);
    }

    private function buildViewData(Game $game, SessionInterface $session, Player $player, Player $dealer): array
    {
        return [
            'player' => $game->valueToString($player->getValueOfHand()),
            'playerCard' => $player->playerToString(),
            'dealer' => $game->valueToString($dealer->getValueOfHand()),
            'dealerCard' => $dealer->playerToString(),
            'buttons' => $player->getStatus(),
            'gameStatus' => $session->get('gameStatus'),
            'result' => $session->get('result'),
        ];
    }

    #[Route('/game/blackjack/hit', name: 'blackjackHit')]
    public function blackjackHit(SessionInterface $session): Response
    {
        $deck = $session->get('deck');
        $player = $session->get('player');

        if ('active' == $session->get('gameStatus')) {
            // Player draws
            $player->addCard($deck->deal(1));
            $session->set('player', $player);
        }
        $session->set('deck', $deck);

        return $this->redirectToRoute('blackjack');
    }

    #[Route('/game/blackjack/stand', name: 'blackjackStand')]
    public function blackjackStand(SessionInterface $session): Response
    {
        $session->set('gameStatus', 'stand');
        $player = $session->get('player');
        $player->changeStatus();
        $session->set('player', $player);

        return $this->redirectToRoute('blackjack');
    }

    #[Route('/game/blackjack/reset', name: 'blackjackReset')]
    public function blackjackReset(SessionInterface $session): Response
    {
        $session->set('gameStatus', 'new');

        return $this->redirectToRoute('blackjack');
    }
}
