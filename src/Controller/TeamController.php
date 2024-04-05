<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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

    #[Route('/fetch', name: 'app_team_fetch', methods: ['GET', 'POST'])]
    public function fetchData(
         EntityManagerInterface $entityManager
        ): Response
    {

        $json_data = json_decode('[
            {
                "id": 1,
                "nom": "Tarbes GB",
                "league": "LFB"
            },
            {
                "id": 2,
                "nom": "Lattes-Montpellier",
                "league": "LFB"
            },
            {
                "id": 3,
                "nom": "Landerneau",
                "league": "LFB"
            },
            {
                "id": 4,
                "nom": "Saint Amand",
                "league": "LFB"
            },
            {
                "id": 5,
                "nom": "ASVEL F",
                "league": "LFB"
            },
            {
                "id": 6,
                "nom": "Tango Bourges",
                "league": "LFB"
            },
            {
                "id": 7,
                "nom": "Basket Landes",
                "league": "LFB"
            },
            {
                "id": 8,
                "nom": "Flammes Carolo",
                "league": "LFB"
            },
            {
                "id": 9,
                "nom": "Roche Vendée",
                "league": "LFB"
            },
            {
                "id": 10,
                "nom": "ESBVA Lille",
                "league": "LFB"
            },
            {
                "id": 11,
                "nom": "UF Angers",
                "league": "LFB"
            },
            {
                "id": 12,
                "nom": "Charnay BBS",
                "league": "LFB"
            },
            {
                "id": 13,
                "nom": "Pole Espoir",
                "league": "LF2"
            },
            {
                "id": 14,
                "nom": "Chartres Basket",
                "league": "LF2"
            },
            {
                "id": 15,
                "nom": "AS Aulnoye",
                "league": "LF2"
            },
            {
                "id": 16,
                "nom": "SIG Basket",
                "league": "LF2"
            },
            {
                "id": 17,
                "nom": "Cavigal Nice",
                "league": "LF2"
            },
            {
                "id": 18,
                "nom": "USO Mondeville",
                "league": "LF2"
            },
            {
                "id": 19,
                "nom": "Toulouse MB",
                "league": "LF2"
            },
            {
                "id": 20,
                "nom": "BB La Tronche-Meylan",
                "league": "LF2"
            },
            {
                "id": 21,
                "nom": "Feytiat Basket",
                "league": "LF2"
            },
            {
                "id": 22,
                "nom": "BC Montbrison",
                "league": "LF2"
            },
            {
                "id": 23,
                "nom": "Champagne Basket",
                "league": "LF2"
            },
            {
                "id": 24,
                "nom": "Pays Voironnais",
                "league": "LF2"
            }
        ]');

        // dd($json_data);

        foreach ($json_data as $data) {
            $team = new Team();
            $league = $entityManager->getRepository('App\Entity\League')->findOneBy(['name' => $data->league]);
            $team->setName($data->nom);
            $team->setLeague($league);
            $entityManager->persist($team);
        }

        $entityManager->flush();
        

        // if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->get('_token'))) {
        //     $entityManager->remove($team);
        //     $entityManager->flush();
        // }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/show/{id}', name: 'app_team_show', methods: ['GET'])]
    public function show(Team $team): Response
    {
        return $this->render('team/show.html.twig', [
            'team' => $team,
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
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($team);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_team_index', [], Response::HTTP_SEE_OTHER);
    }
}
