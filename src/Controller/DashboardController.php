<?php

namespace App\Controller;

use App\Repository\ChoiceRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Repository\WeekRepository;
use App\Service\WeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private WeekService $weekService;
    private EntityManagerInterface $entityManager;

    public function __construct(WeekService $weekService, EntityManagerInterface $entityManager)
    {
        $this->weekService = $weekService;
        $this->entityManager = $entityManager;
    }

    #[Route('/dashboard/week/{id}', name: 'app_dashboard_id', methods: ['GET'])]
    public function index(
        int $id,
        WeekRepository $weekRepository,
        ChoiceRepository $choiceRepository,
        TeamRepository $teamRepository
    ): Response {
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
    
      // Calculate the remaining time until the deadline
    $deadline = new \DateTime($this->weekService->getDeadlineForWeek($id));
    $now = new \DateTime();
    $remainingTime = $deadline->diff($now);

    // Determine if the current time is before or after the deadline
    $timeIsValid = $now < $deadline;

    if ($timeIsValid) {
        // Extract months, days, hours, minutes, and seconds if the deadline hasn't passed
        $remainingMonths = $remainingTime->m;
        $remainingDays = $remainingTime->days;
        $remainingHours = $remainingTime->h;
        $remainingMinutes = $remainingTime->i;
        $remainingSeconds = $remainingTime->s;
    } else {
        // Set all time components to zero if the deadline has passed
        $remainingMonths = 0;
        $remainingDays = 0;
        $remainingHours = 0;
        $remainingMinutes = 0;
        $remainingSeconds = 0;
    }
        // Obtenir les joueurs sélectionnés pour la semaine spécifique dans la table Choice
        $choices = $choiceRepository->findBy(['week' => $week]);
        $selectedPlayers = array_map(fn($choice) => $choice->getPlayer(), $choices);
    
        // Calculer les points pour la semaine
        $totalPoints = array_reduce($choices, fn($carry, $choice) => $carry + $choice->getPoints(), 0);
    
        // Remplacer les IDs des équipes par leurs noms dans les matchs filtrés
        $this->replaceTeamIdsWithNames($matchesLFBFiltered, $teamRepository);
        $this->replaceTeamIdsWithNames($matchesLF2Filtered, $teamRepository);
    
        return $this->render('dashboard/index.html.twig', [
            'week' => $week,
            'matchesLFB' => $matchesLFBFiltered,
            'matchesLF2' => $matchesLF2Filtered,
            'selectedPlayers' => $selectedPlayers,
            'remainingMonths' => $timeIsValid ? $remainingMonths : 0,
            'remainingDays' => $timeIsValid ? $remainingDays : 0,
            'remainingHours' => $timeIsValid ? $remainingHours : 0,
            'remainingMinutes' => $timeIsValid ? $remainingMinutes : 0,
            'remainingSeconds' => $timeIsValid ? $remainingSeconds : 0,
            'totalPoints' => $totalPoints,
            'timeIsValid' => $timeIsValid,
        ]);
    }
    
    private function replaceTeamIdsWithNames(array &$matches, TeamRepository $teamRepository): void
    {
        foreach ($matches as &$match) {
            $homeTeam = $teamRepository->find($match['home_team_id']);
            $awayTeam = $teamRepository->find($match['away_team_id']);
    
            if ($homeTeam && $awayTeam) {
                $match['home_team_name'] = $homeTeam->getName();
                $match['away_team_name'] = $awayTeam->getName();
            }
        }
    }
    
    
    #[Route('/dashboard', name: 'app_dashboard')]
    public function show(Request $request, UserRepository $userRepository, WeekRepository $weekRepository, ChoiceRepository $choiceRepository): Response
    {
        // Gestion de la session pour stocker les données des semaines
        $session = $request->getSession();
        $weeksData = $this->weekService->getWeeksData();
        $session->set('weeksLFB', $weeksData['weeksLFB']);
        $session->set('weeksLF2', $weeksData['weeksLF2']);

        // Récupération de l'utilisateur connecté
        $currentUser = $this->getUser();

        // Récupérer les utilisateurs triés par points LFB
        $usersLFB = $userRepository->findBy([], ['ptl_lfb' => 'DESC']);
        $usersLF2 = $userRepository->findBy([], ['pt_lf2' => 'DESC']);

        // Calcul du rang de l'utilisateur connecté
        $rankLFB = array_search($currentUser, $usersLFB) + 1;
        $rankLF2 = array_search($currentUser, $usersLF2) + 1;

        // Trouver la dernière semaine remplie pour LFB et LF2
        $latestWeekLFB = $weekRepository->findLatestFilledWeek(1, 22); // Pour LFB, semaines 1-22
        $latestWeekLF2 = $weekRepository->findLatestFilledWeek(23, 44); // Pour LF2, semaines 23-44

        // Obtenir les choix et calculer les points pour les semaines correspondantes
        $choicesLFB = $choiceRepository->findBy(['week' => $latestWeekLFB, 'user' => $currentUser]);
        $choicesLF2 = $choiceRepository->findBy(['week' => $latestWeekLF2, 'user' => $currentUser]);

        $totalPointsLFB = array_reduce($choicesLFB, fn($carry, $choice) => $carry + $choice->getPoints(), 0);
        $totalPointsLF2 = array_reduce($choicesLF2, fn($carry, $choice) => $carry + $choice->getPoints(), 0);

        return $this->render('dashboard/show.html.twig', [
            'controller_name' => 'DashboardController',
            'rankLFB' => $rankLFB,
            'totalUsersLFB' => count($usersLFB),
            'rankLF2' => $rankLF2,
            'totalUsersLF2' => count($usersLF2),
            'latestWeekLFB' => $latestWeekLFB,
            'latestWeekLF2' => $latestWeekLF2,
            'totalPointsLFB' => $totalPointsLFB,
            'totalPointsLF2' => $totalPointsLF2,
        ]);
    }

    #[Route('/dashboard/delete-player/{weekId}/{playerId}', name: 'app_dashboard_delete_player', methods: ['DELETE'])]
    public function deletePlayer(int $weekId, int $playerId, ChoiceRepository $choiceRepository): Response
    {
        try {
            // Trouver l'entité Choice par joueur et semaine
            $choice = $choiceRepository->findOneBy(['player' => $playerId, 'week' => $weekId]);

            if (!$choice) {
                return new Response('Player not found in the DECK for the specified week.', Response::HTTP_NOT_FOUND);
            }

            // Supprimer l'entité Choice
            $this->entityManager->remove($choice);
            $this->entityManager->flush();

            return new Response('Player successfully removed from the DECK.', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while deleting the player: ' . $e->getMessage());
            return new Response('An error occurred while deleting the player.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    #[Route('/ranking/week', name: 'ranking_week')]
    public function rankingWeek(Request $request, UserRepository $userRepository, WeekRepository $weekRepository): Response
    {
        // Get the league from the URL query parameter, default to 'lfb' if not provided
        $league = $request->query->get('league', 'lfb');
    
        // Define week ranges based on the league
        if ($league === 'lfb') {
            $startWeek = 1;
            $endWeek = 22;
        } elseif ($league === 'lf2') {
            $startWeek = 23;
            $endWeek = 44;
        } else {
            throw $this->createNotFoundException('Invalid league specified.');
        }
    
        // Find the latest filled week within the specified range
        $latestWeek = $weekRepository->findLatestFilledWeek($startWeek, $endWeek);
    
        if (!$latestWeek) {
            throw $this->createNotFoundException('No filled week found for the specified league.');
        }
    
        // Get users ordered by their points in the specified league
        $users = $userRepository->findAllOrderedByPoints($league);
    
        // Render the ranking page with the retrieved data
        return $this->render('dashboard/ranking.week.html.twig', [
            'week' => $latestWeek,
            'users' => $users,
            'league' => $league,
        ]);
    }
    
}
