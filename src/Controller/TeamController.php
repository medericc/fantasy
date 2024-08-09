<?php

namespace App\Controller;

use App\Entity\Choice;

use App\Entity\User;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\ChoiceRepository;
use App\Repository\TeamRepository;
use App\Repository\PlayerRepository;
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
                $entityManager->persist($choice);
            }
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'success', 'message' => 'Players successfully saved']);

    } catch (\Exception $e) {
        return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

}
