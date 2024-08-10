<?php

namespace App\Controller;

use App\Entity\Choice;

use App\Entity\User;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\ChoiceRepository;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
use App\Repository\UserRepository;
use App\Repository\WeekRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/team')]
class TeamController extends AbstractController
{
    private $datesLimites = [
        1 => '2024-09-21 23:00:00',  // Semaine 1
        2 => '2024-09-21 23:00:00',  // Semaine 2
        3 => '2024-09-21 23:00:00',  // Semaine 3
        4 => '2024-09-21 23:00:00',  // Semaine 4
        5 => '2024-09-21 23:00:00',  // Semaine 5
        6 => '2024-09-21 23:00:00',  // Semaine 6
        7 => '2024-09-21 23:00:00',  // Semaine 7
        8 => '2024-09-21 23:00:00',  // Semaine 8
        9 => '2024-09-21 23:00:00',  // Semaine 9
        10 => '2024-09-21 23:00:00', // Semaine 10
        11 => '2024-09-21 23:00:00', // Semaine 11
        12 => '2024-09-21 23:00:00', // Semaine 12
        13 => '2024-09-21 23:00:00', // Semaine 13
        14 => '2024-09-21 23:00:00', // Semaine 14
        15 => '2024-09-21 23:00:00', // Semaine 15
        16 => '2024-09-21 23:00:00', // Semaine 16
        17 => '2024-09-21 23:00:00', // Semaine 17
        18 => '2024-09-21 23:00:00', // Semaine 18
        19 => '2024-09-21 23:00:00', // Semaine 19
        20 => '2024-09-21 23:00:00', // Semaine 20
        21 => '2024-09-21 23:00:00', // Semaine 21
        22 => '2024-09-21 23:00:00', // Semaine 22
        23 => '2024-09-21 23:00:00', // Semaine 23
        24 => '2024-09-21 23:00:00', // Semaine 24
        25 => '2024-09-21 23:00:00', // Semaine 25
        26 => '2024-09-21 23:00:00', // Semaine 26
        27 => '2024-09-21 23:00:00', // Semaine 27
        28 => '2024-09-21 23:00:00', // Semaine 28
        29 => '2024-09-21 23:00:00', // Semaine 29
        30 => '2024-09-21 23:00:00', // Semaine 30
        31 => '2024-09-21 23:00:00', // Semaine 31
        32 => '2024-09-21 23:00:00', // Semaine 32
        33 => '2024-09-21 23:00:00', // Semaine 33
        34 => '2024-09-21 23:00:00', // Semaine 34
        35 => '2024-09-21 23:00:00', // Semaine 35
        36 => '2024-09-21 23:00:00', // Semaine 36
        37 => '2024-09-21 23:00:00', // Semaine 37
        38 => '2024-09-21 23:00:00', // Semaine 38
        39 => '2024-09-21 23:00:00', // Semaine 39
        40 => '2024-09-21 23:00:00', // Semaine 40
        41 => '2024-09-21 23:00:00', // Semaine 41
        42 => '2024-09-21 23:00:00', // Semaine 42
        43 => '2024-09-21 23:00:00', // Semaine 43
        44 => '2024-09-21 23:00:00', // Semaine 44
    ];
    

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
        'weekId' => $weekId, // Pass weekId to the template
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
        $dateLimite = new \DateTime($this->datesLimites[$weekId]);
        $maintenant = new \DateTime();

        if ($maintenant > $dateLimite) {
            return new JsonResponse(['status' => 'error', 'message' => 'La date limite pour l\'ajout de joueurs pour cette semaine est dépassée.'], 403);
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['status' => 'error', 'message' => 'User not authenticated'], 401);
        }

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

            $recentChoices = $choiceRepository->findRecentChoicesForPlayer($playerId, $week->getId(), 5, $user->getId());
            if (!empty($recentChoices)) {
                return new JsonResponse(['status' => 'error', 'message' => 'Player ' . $playerData['forename'] . ' ' . $playerData['name'] . ' is blocked for the next 5 weeks.']);
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

    $users = $userRepository->findAllOrderedByPoints($league);

    return $this->render('team/ranking.html.twig', [
        'users' => $users,
        'league' => $league,
    ]);
}
    

}
