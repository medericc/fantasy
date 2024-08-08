<?php
namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Team;
use App\Form\TeamType;
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
    public function show(Team $team, PlayerRepository $playerRepository, Request $request): Response
    {
        $weekId = $request->query->get('weekId');
        $players = $playerRepository->findAll();
    
        return $this->render('team/show.html.twig', [
            'team' => $team,
            'players' => $players,
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
        EntityManagerInterface $entityManager
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
    
            if ($data === null) {
                return new JsonResponse(['status' => 'error', 'message' => 'Invalid JSON'], 400);
            }
    
            $savedPlayers = [];
            foreach ($data['players'] as $playerData) {
                $player = $playerRepository->find($playerData['id']);
                if ($player) {
                    $choice = new Choice();
                    $choice->setUser($this->getUser());
    
                    if (isset($playerData['weekId']) && !empty($playerData['weekId'])) {
                        $week = $weekRepository->find($playerData['weekId']);
                        if ($week) {
                            $choice->setWeek($week);
                        } else {
                            return new JsonResponse(['status' => 'error', 'message' => 'Invalid week ID'], 400);
                        }
                    }
    
                    $choice->addPlayer($player);
                    $entityManager->persist($choice);
    
                    $savedPlayers[] = [
                        'id' => $player->getId(),
                        'forename' => $player->getForename(),
                        'name' => $player->getName(),
                    ];
                }
            }
    
            $entityManager->flush();
    
            return new JsonResponse(['status' => 'success', 'players' => $savedPlayers]);
    
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    
    
}
