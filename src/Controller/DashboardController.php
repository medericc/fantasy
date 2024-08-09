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

        return $this->render('dashboard/index.html.twig', [
            'weekId' => $id,
            'week' => $week,
            'matchesLF2' => $matchesLF2Filtered,
            'matchesLFB' => $matchesLFBFiltered,
            'selectedPlayers' => $selectedPlayers,
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

    #[Route('/dashboard/delete-player/{id}', name: 'app_dashboard_delete_player', methods: ['DELETE'])]
    public function deletePlayer(int $id, ChoiceRepository $choiceRepository): Response
    {
        try {
            // Récupérer l'entité Choice correspondante
            $choice = $choiceRepository->find($id);
            if (!$choice) {
                return new Response('Player not found in the DECK.', Response::HTTP_NOT_FOUND);
            }

            // Supprimer le joueur de la table Choice
            $this->entityManager->remove($choice);
            $this->entityManager->flush();

            return new Response('Player successfully removed from the DECK.', Response::HTTP_OK);
        } catch (\Exception $e) {
            // Gestion des exceptions avec log d'erreur
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du joueur: ' . $e->getMessage());
            return new Response('Une erreur s\'est produite lors de la suppression du joueur.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
