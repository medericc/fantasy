<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Player;
use App\Entity\PlayerRate;
use App\Entity\Week;
use App\Entity\User;
use App\Form\PlayerRateType;
use App\Repository\ChoiceRepository;
use App\Repository\PlayerRateRepository;
use App\Repository\PlayerRepository;
use App\Repository\WeekRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerRateController extends AbstractController
{
    #[Route('/admin/assign-points/{playerId}/{weekId}', name: 'assign_points', methods: ['GET', 'POST'])]
    public function assignPoints(
        int $playerId,
        int $weekId,
        Request $request,
        PlayerRepository $playerRepository,
        WeekRepository $weekRepository,
        EntityManagerInterface $entityManager,
        PlayerRateRepository $playerRateRepository
    ): Response {
        $player = $playerRepository->find($playerId);
        $week = $weekRepository->find($weekId);
    
        if (!$player || !$week) {
            throw $this->createNotFoundException('Player or Week not found');
        }
    
        $playerRate = $playerRateRepository->findOneBy([
            'player' => $player,
            'week' => $week,
        ]) ?? new PlayerRate();
    
        $playerRate->setPlayer($player);
        $playerRate->setWeek($week);
    
        $form = $this->createForm(PlayerRateType::class, $playerRate);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($playerRate);
            $entityManager->flush();
    
            // Mettre à jour le rating du joueur après avoir persisté le PlayerRate
            $player->updateRating($entityManager);
    
            // Mettre à jour tous les choix pour ce joueur et cette semaine
            $this->updateChoicesPoints($player, $week, $entityManager);
    
            $this->addFlash('success', 'Points assigned successfully.');
    
            return $this->redirectToRoute('app_team_show', ['id' => $player->getTeam()->getId(), 'weekId' => $weekId]);
        }
    
        return $this->render('admin/assign_points.html.twig', [
            'form' => $form->createView(),
            'player' => $player,
            'week' => $week,
        ]);
    }
    
    /**
     * Met à jour les points pour tous les choix existants pour un joueur et une semaine donnés.
     */
    private function updateChoicesPoints(Player $player, Week $week, EntityManagerInterface $entityManager): void
    {
        $choices = $entityManager->getRepository(Choice::class)->findBy([
            'player' => $player,
            'week' => $week,
        ]);
    
        foreach ($choices as $choice) {
            $choice->updatePoints($entityManager);
            $entityManager->persist($choice);
        }
    
        $entityManager->flush();
    }
    
    /**
     * Sauvegarde les joueurs et met à jour les points cumulés de l'utilisateur.
     */
    #[Route('/admin/save-players', name: 'save_players', methods: ['POST'])]
    public function savePlayers(
        Request $request,
        PlayerRepository $playerRepository,
        WeekRepository $weekRepository,
        ChoiceRepository $choiceRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Logique de sauvegarde des choix (à implémenter)
        
        // Après avoir sauvegardé les choix, mettre à jour les points cumulés de l'utilisateur
        $user = $this->getUser();
        $this->updateUserPoints($user, $entityManager);
        
        return new JsonResponse(['status' => 'success', 'message' => 'Players successfully saved']);
    }
    
    /**
     * Met à jour les points cumulés pour l'utilisateur.
     */
    private function updateUserPoints(User $user, EntityManagerInterface $entityManager): void
    {
        $choices = $entityManager->getRepository(Choice::class)->findBy(['user' => $user]);

        // Mise à jour des points cumulés pour les deux périodes
        $user->updateCumulativePoints($choices);

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
