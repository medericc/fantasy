<?php

namespace App\Controller;

use App\Entity\League;
use App\Form\LeagueType;
use App\Repository\LeagueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/league')]
class LeagueController extends AbstractController
{
    #[Route('/', name: 'app_league_index', methods: ['GET'])]
    public function index(LeagueRepository $leagueRepository): Response
    {
        return $this->render('league/index.html.twig', [
            'leagues' => $leagueRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_league_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $league = new League();
        $form = $this->createForm(LeagueType::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($league);
            $entityManager->flush();

            return $this->redirectToRoute('app_league_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league/new.html.twig', [
            'league' => $league,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_league_show', methods: ['GET'])]
    public function show(League $league): Response
    {
        return $this->render('league/show.html.twig', [
            'league' => $league,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_league_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LeagueType::class, $league);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_league_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('league/edit.html.twig', [
            'league' => $league,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_league_delete', methods: ['POST'])]
    public function delete(Request $request, League $league, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$league->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($league);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_league_index', [], Response::HTTP_SEE_OTHER);
    }
}
