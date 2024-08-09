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
        // Load match data from JSON files
        $matchesLFB = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/matchlfb.json'), true);
        $matchesLF2 = json_decode(file_get_contents($this->getParameter('kernel.project_dir') . '/matchlf2.json'), true);

        // Filter matches based on week ID
        $matchesLFBFiltered = $id <= 22 ? $matchesLFB[$id] ?? [] : [];
        $matchesLF2Filtered = $id > 22 ? $matchesLF2[$id] ?? [] : [];

        // Retrieve the week entity
        $week = $weekRepository->find($id);
        if (!$week) {
            throw $this->createNotFoundException('Week not found');
        }

        // Get the selected players for the specific week from the Choice table
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
        $choice = $choiceRepository->find($id);
        if (!$choice) {
            return new Response('Player not found in the DECK.', Response::HTTP_NOT_FOUND);
        }

        // Remove the player from the Choice table
        $this->entityManager->remove($choice);
        $this->entityManager->flush();

        return new Response('Player successfully removed from the DECK.', Response::HTTP_OK);
    }
}
