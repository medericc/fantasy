<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Repository\ChoiceRepository;
use App\Repository\WeekRepository;
use App\Service\WeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private $weekService;
    private $entityManager;

    public function __construct(WeekService $weekService, EntityManagerInterface $entityManager)
    {
        $this->weekService = $weekService;
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard/week/{id}', name: 'app_dashboard_id', methods: ['GET'])]
    public function index(int $id, WeekRepository $weekRepository, ChoiceRepository $choiceRepository): Response
    {
        // Charger les données des matchs à partir des fichiers JSON
        $matchesLFB = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/matchlfb.json'), true);
        $matchesLF2 = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/matchlf2.json'), true);
    
        // Filtrer les matchs en fonction de l'ID de la semaine
        $matchesLFBFiltered = $id <= 22 ? $matchesLFB[$id] ?? [] : [];
        $matchesLF2Filtered = $id > 22 ? $matchesLF2[$id] ?? [] : [];
    
        // Récupérer l'entité Week correspondante
        $week = $weekRepository->find($id);
        if (!$week) {
            throw $this->createNotFoundException('Week not found');
        }
    
        // Obtenir les joueurs sélectionnés pour la semaine spécifique dans la table Choice
        $choices = $choiceRepository->findBy(['week' => $week]);
        $selectedPlayers = array_map(fn($choice) => $choice->getPlayer(), $choices);
    
        // Calculer les points pour la semaine
        $totalPoints = array_reduce($choices, function($carry, $choice) {
            return $carry + $choice->getPoints(); // Supposant que getPoints() retourne le score du joueur pour cette semaine
        }, 0);
    
        return $this->render('dashboard/index.html.twig', [
            'weekId' => $id,
            'week' => $week,
            'matchesLF2' => $matchesLF2Filtered,
            'matchesLFB' => $matchesLFBFiltered,
            'selectedPlayers' => $selectedPlayers,
            'totalPoints' => $totalPoints, // Passer les points à la vue
        ]);
    }
    

    #[Route('/dashboard', name: 'app_dashboard')]
    public function show(Request $request): Response
    {
        // Gestion de la session pour stocker les données des semaines
        $session = $request->getSession();
        $weeksData = $this->weekService->getWeeksData();
        $session->set('weeksLFB', $weeksData['weeksLFB']);
        $session->set('weeksLF2', $weeksData['weeksLF2']);

        return $this->render('dashboard/show.html.twig', [
            'controller_name' => 'DashboardController',
        ]);
    }

    #[Route('/dashboard/delete-player/{weekId}/{playerId}', name: 'app_dashboard_delete_player', methods: ['DELETE'])]
    public function deletePlayer(int $weekId, int $playerId, ChoiceRepository $choiceRepository): Response
    {
        try {
            // Find the Choice entity by player and week
            $choice = $choiceRepository->findOneBy(['player' => $playerId, 'week' => $weekId]);
    
            if (!$choice) {
                return new Response('Player not found in the DECK for the specified week.', Response::HTTP_NOT_FOUND);
            }
    
            // Remove the Choice entity
            $this->entityManager->remove($choice);
            $this->entityManager->flush();
    
            return new Response('Player successfully removed from the DECK.', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while deleting the player: ' . $e->getMessage());
            return new Response('An error occurred while deleting the player.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    
}
 