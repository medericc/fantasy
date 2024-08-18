<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Player;
use App\Entity\User;
use App\Entity\Week;
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
    
        // Calculer le temps restant jusqu'à la date limite
        $deadline = new \DateTime($this->weekService->getDeadlineForWeek($id));
        $now = new \DateTime();
        $remainingTime = $deadline->diff($now);
    
        $timeIsValid = $now < $deadline;
    
        // Calcul du temps restant
        $remainingMonths = $timeIsValid ? $remainingTime->m : 0;
        $remainingDays = $timeIsValid ? $remainingTime->days : 0;
        $remainingHours = $timeIsValid ? $remainingTime->h : 0;
        $remainingMinutes = $timeIsValid ? $remainingTime->i : 0;
        $remainingSeconds = $timeIsValid ? $remainingTime->s : 0;
    
        // Récupérer l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();
    
        // Obtenir les choix de l'utilisateur connecté pour la semaine spécifique
        $choices = $choiceRepository->findBy(['week' => $week, 'user' => $user]);
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
            'remainingMonths' => $remainingMonths,
            'remainingDays' => $remainingDays,
            'remainingHours' => $remainingHours,
            'remainingMinutes' => $remainingMinutes,
            'remainingSeconds' => $remainingSeconds,
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
    public function deletePlayer(int $weekId, int $playerId, ChoiceRepository $choiceRepository, EntityManagerInterface $entityManager): Response
    {
        try {
            // Trouver l'entité Choice par joueur et semaine
            $choice = $choiceRepository->findOneBy(['player' => $playerId, 'week' => $weekId]);
    
            if (!$choice) {
                return new Response('Player not found in the DECK for the specified week.', Response::HTTP_NOT_FOUND);
            }
    
            // Supprimer l'entité Choice
            $entityManager->remove($choice);
            $entityManager->flush();
    
         // Recalculer les points de l'utilisateur après la suppression
$user = $this->getUser();
if ($user instanceof User) {
    // Appeler la méthode en passant d'abord l'EntityManager
    $this->updateUserPoints($entityManager);
}

    
            return new Response('Player successfully removed from the DECK and points updated.', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while deleting the player: ' . $e->getMessage());
            return new Response('An error occurred while deleting the player.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    private function updateUserPoints(EntityManagerInterface $entityManager): void
    {
        // Récupérer tous les utilisateurs
        $users = $entityManager->getRepository(User::class)->findAll();
    
        foreach ($users as $user) {
            // Récupérer tous les choix de cet utilisateur
            $choices = $entityManager->getRepository(Choice::class)->findBy(['user' => $user]);
    
            $totalLfbPoints = 0;
            $totalLf2Points = 0;
    
            foreach ($choices as $choice) {
                $week = $choice->getWeek();
                $points = $choice->getPoints();
    
                // Calculer les points pour LFB et LF2 en fonction de l'ID de la semaine
                if ($week->getId() <= 22) {
                    $totalLfbPoints += $points;
                } else {
                    $totalLf2Points += $points;
                }
            }
    
            // Mettre à jour les points cumulés de l'utilisateur
            $user->setPtlLfb($totalLfbPoints);
            $user->setPtLf2($totalLf2Points);
    
            // Persister les changements pour cet utilisateur
            $entityManager->persist($user);
        }
    
        // Après avoir parcouru tous les utilisateurs, enregistrer les changements dans la base de données
        $entityManager->flush();
    }
    
    
    #[Route('/ranking/week', name: 'ranking_week')]
    public function rankingWeek(Request $request, UserRepository $userRepository, WeekRepository $weekRepository, ChoiceRepository $choiceRepository): Response
    {
        // Obtenir la ligue à partir du paramètre d'URL, par défaut 'lfb'
        $league = $request->query->get('league', 'lfb');
        
        // Définir les plages de semaines en fonction de la ligue
        if ($league === 'lfb') {
            $startWeek = 1;
            $endWeek = 22;
        } elseif ($league === 'lf2') {
            $startWeek = 23;
            $endWeek = 44;
        } else {
            throw $this->createNotFoundException('Invalid league specified.');
        }
    
        // Trouver la dernière semaine remplie dans la plage spécifiée
        $latestWeek = $weekRepository->findLatestFilledWeek($startWeek, $endWeek);
    
        if (!$latestWeek) {
            throw $this->createNotFoundException('No filled week found for the specified league.');
        }
    
        // Récupérer les choix des utilisateurs pour la dernière semaine
        $choices = $choiceRepository->findBy(['week' => $latestWeek]);
    
        // Calculer les points pour chaque utilisateur
        $userPoints = [];
        foreach ($choices as $choice) {
            $userId = $choice->getUser()->getId();
            if (!isset($userPoints[$userId])) {
                $userPoints[$userId] = 0;
            }
            $userPoints[$userId] += $choice->getPoints();
        }
    
        // Récupérer les utilisateurs par leurs IDs et trier par points
        $users = $userRepository->findBy(['id' => array_keys($userPoints)], []);
        usort($users, function($a, $b) use ($userPoints) {
            return $userPoints[$b->getId()] <=> $userPoints[$a->getId()];
        });
    
        // Associer les points calculés aux utilisateurs pour les passer au template
        $usersWithPoints = [];
        foreach ($users as $user) {
            $usersWithPoints[] = [
                'user' => $user,
                'pointsForWeek' => $userPoints[$user->getId()]
            ];
        }
    
        return $this->render('dashboard/ranking.week.html.twig', [
            'week' => $latestWeek,
            'usersWithPoints' => $usersWithPoints,
            'league' => $league,
            
            'hasData' => !empty($usersWithPoints) && $latestWeek !== null,
        ]);
    }
    
}
