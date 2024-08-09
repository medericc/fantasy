<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Repository\ChoiceRepository;
use App\Repository\PlayerRepository;
use App\Repository\WeekRepository;
use App\Service\WeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(private readonly WeekService $weekService) {}

    #[Route('/dashboard/week/{id}', name: 'app_dashboard_id', methods: ['GET'])]
    public function index(int $id, PlayerRepository $playerRepository, WeekRepository $weekRepository, ChoiceRepository $choiceRepository): Response
    {
        $matchesLFB = json_decode(file_get_contents('../matchlfb.json'), true);
        $matchesLF2 = json_decode(file_get_contents('../matchlf2.json'), true);

        $matchesLFBFiltered = [];
        $matchesLF2Filtered = [];
        if ($id <= 22) {
            $matchesLFBFiltered = $matchesLFB[$id];
        } else {
            $matchesLF2Filtered = $matchesLF2[$id];
        }

        // Retrieve the week entity
        $week = $weekRepository->find($id);
        if (!$week) {
            throw $this->createNotFoundException('Week not found');
        }

        // Get the selected players for the specific week from the Choice table
        $choices = $choiceRepository->findBy(['week' => $week]);
        $selectedPlayers = [];
        foreach ($choices as $choice) {
            $selectedPlayers[] = $choice->getPlayer();
        }

        return $this->render('dashboard/index.html.twig', [
            'weekId' => $id, // Pass the weekId to the template
            'week' => $week, // Ensure the 'week' variable is passed to the template
            'matchesLF2' => $matchesLF2Filtered,
            'matchesLFB' => $matchesLFBFiltered,
            'selectedPlayers' => $selectedPlayers // Pass selected players to the template
        ]);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function show(Request $request): Response
    {
        $session = $request->getSession();
        $weeksData = $this->weekService->getWeeksData();
        $weeksLF2 = $weeksData['weeksLF2'];
        $weeksLFB = $weeksData['weeksLFB'];
        $session->set('weeksLFB', $weeksLFB);
        $session->set('weeksLF2', $weeksLF2);

        return $this->render('dashboard/show.html.twig', [
            'controller_name' => 'DashboardController'
        ]);
    }
}
