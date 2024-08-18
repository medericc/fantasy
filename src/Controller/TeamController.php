<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Team;
use App\Entity\User;
use App\Form\TeamType;
use App\Repository\ChoiceRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Repository\UserRepository;
use App\Repository\WeekRepository;
use App\Service\WeekService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/team')]
class TeamController extends AbstractController
{
    private WeekService $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }

    #[Route('/', name: 'app_team_index', methods: ['GET'])]
    public function index(TeamRepository $teamRepository): Response
    {
        return $this->render('team/index.html.twig', [
            'teams' => $teamRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_team_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($team);
            $entityManager->flush();

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/new.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/show/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(
        Team $team,
        PlayerRepository $playerRepository,
        Request $request,
        ChoiceRepository $choiceRepository,
        WeekRepository $weekRepository
    ): Response {
        $weekId = $request->query->get('weekId');
        $week = $weekRepository->find($weekId);

        if (!$week) {
            throw $this->createNotFoundException('Week not found');
        }

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User not found');
        }

        $choices = $choiceRepository->findBy(['week' => $week, 'user' => $user]);
        $selectedPlayers = array_map(fn($choice) => $choice->getPlayer(), $choices);

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'players' => $playerRepository->findAll(),
            'selectedPlayers' => $selectedPlayers,
            'weekId' => $weekId,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_team_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('team/edit.html.twig', [
            'team' => $team,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_team_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $team->getId(), $request->get('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }

 
    #[Route('/save-players', name: 'app_team_save_players', methods: ['POST'])]
    public function savePlayers(
        Request $request,
        PlayerRepository $playerRepository,
        WeekRepository $weekRepository,
        ChoiceRepository $choiceRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
    
            if ($data === null) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid JSON'], 400);
            }
    
            $weekId = $request->query->get('weekId');
            if (!$weekId) {
                return new JsonResponse(['status' => 'error', 'message' => 'Week ID is required'], 400);
            }
    
            $week = $weekRepository->find($weekId);
            if (!$week) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid week ID'], 400);
            }
    
            // Vérifier la date limite pour cette semaine
            $dateLimite = new \DateTime($this->weekService->getDeadlineForWeek($weekId));
            $maintenant = new \DateTime();
    
            if ($maintenant > $dateLimite) {
                return new JsonResponse(['status' => 'error', 'message' => 'La date limite pour l\'ajout de joueurs pour cette semaine est dépassée.'], 403);
            }
    
            /** @var User $user */
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], 401);
            }
    
            // Fetch existing choices for the authenticated user and the specified week
            $existingChoices = $choiceRepository->findBy(['week' => $week, 'user' => $user]);
            $existingPlayerIds = array_map(fn($choice) => $choice->getPlayer()->getId(), $existingChoices);
    
            $totalPlayersSelected = count($existingPlayerIds) + count($data['players']);
            if ($totalPlayersSelected > 5) {
                return new JsonResponse(['status' => 'error', 'message' => 'You cannot select more than 5 players in total'], 400);
            }
    
            foreach ($data['players'] as $playerData) {
                $playerId = $playerData['id'];
    
                if (in_array($playerId, $existingPlayerIds)) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Player ' . $playerData['forename'] . ' ' . $playerData['name'] . ' has already been selected for this week'], 400);
                }
    
                // Block the player for 5 weeks before and after the current week
                $blockedWeeksRange = range($week->getId() - 5, $week->getId() + 5);
                $recentChoices = $choiceRepository->findChoicesForPlayerInWeeks($playerId, $blockedWeeksRange, $user->getId());
    
                if (!empty($recentChoices)) {
                    return new JsonResponse(['status' => 'error', 'message' => 'Player ' . $playerData['forename'] . ' ' . $playerData['name'] . ' is blocked for 5 weeks before and after the selected week.']);
                }
    
                $player = $playerRepository->find($playerId);
                if ($player) {
                    $choice = new Choice();
                    $choice->setUser($user);
                    $choice->setWeek($week);
                    $choice->setPlayer($player);
    
                    // Mettre à jour les points du choix avec la méthode updatePoints
                    $choice->updatePoints($entityManager);
    
                    $entityManager->persist($choice);
                }
            }
    
            $entityManager->flush();
    
            return new JsonResponse(['status' => 'success', 'message' => 'Players successfully saved']);
    
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    
    

    #[Route('/ranking/{league}', name: 'app_team_ranking', methods: ['GET'])]
    public function ranking(string $league, UserRepository $userRepository): Response
    {
        // Valide le type de league
        if (!in_array($league, ['lfb', 'lf2'])) {
            throw $this->createNotFoundException('Invalid league');
        }
    
        // Récupérer l'utilisateur connecté
        $currentUser = $this->getUser();
    
        // Vérifie si l'utilisateur est bien connecté et est une instance de User
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to view the ranking.');
        }
    
        // Récupérer les utilisateurs triés par points selon la league
        $users = $userRepository->findAllOrderedByPoints($league);
    
        // Trouver la position de l'utilisateur connecté
        $userRank = null;
        foreach ($users as $index => $user) {
            // Assure que $user est bien une instance de User
            if ($user instanceof User && $user->getId() === $currentUser->getId()) {
                $userRank = $index + 1;
                break;
            }
        }
    
        return $this->render('dashboard/ranking.html.twig', [
            'users' => $users,
            'league' => $league,
            'currentUser' => $currentUser,
            'userRank' => $userRank,
        ]);
    }
}
