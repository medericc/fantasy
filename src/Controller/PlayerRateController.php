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
        ChoiceRepository $choiceRepository,
        EntityManagerInterface $entityManager,
        PlayerRateRepository $playerRateRepository
    ): Response {
        $player = $playerRepository->find($playerId);
        $week = $weekRepository->find($weekId);
    
        if (!$player || !$week) {
            throw $this->createNotFoundException('Player or Week not found');
        }
    
        // Récupérer ou créer un nouveau PlayerRate pour ce joueur et cette semaine
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
    
            // Mettre à jour le rating du joueur
            $player->updateRating($entityManager);
    
            // Mettre à jour les points pour tous les choix de ce joueur pour cette semaine
            $this->updateChoicesPoints($player, $week, $entityManager);

            // Mettre à jour les points cumulés de l'utilisateur
            $user = $this->getUser();
            if ($user instanceof User) {
                $this->updateUserPoints($user, $entityManager);
            }
    
            $this->addFlash('success', 'Points assigned successfully.');
    
            return $this->redirectToRoute('app_team_show', ['id' => $player->getTeam()->getId(), 'weekId' => $weekId]);
        }
    
        return $this->render('admin/assign_points.html.twig', [
            'form' => $form->createView(),
            'player' => $player,
            'week' => $week,
        ]);
    }
    
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
    
    private function updateUserPoints(User $user, EntityManagerInterface $entityManager): void
    {
        $choices = $entityManager->getRepository(Choice::class)->findBy(['user' => $user]);
    
        $totalLfbPoints = 0;
        $totalLf2Points = 0;
    
        foreach ($choices as $choice) {
            $week = $choice->getWeek();
            $points = $choice->getPoints();
    
            if ($week->getId() <= 22) {
                $totalLfbPoints += $points;
            } else {
                $totalLf2Points += $points;
            }
        }
    
        $user->setPtlLfb($totalLfbPoints);
        $user->setPtLf2($totalLf2Points);
    
        $entityManager->persist($user);
        $entityManager->flush();
    }
    
}
