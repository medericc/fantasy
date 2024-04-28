<?php

namespace App\Controller;

use App\Entity\Choice;
use App\Entity\Week;
use App\Form\ChoiceType;
use App\Form\WeekType;
use App\Repository\ChoiceRepository;
use App\Repository\TeamRepository;
use App\Repository\WeekRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/week')]
class WeekController extends AbstractController
{
    #[Route('/', name: 'app_week_index', methods: ['GET'])]
    public function index(WeekRepository $weekRepository): Response
    {
        return $this->render('week/index.html.twig', [
            'weeks' => $weekRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_week_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $week = new Week();
        $form = $this->createForm(WeekType::class, $week);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($week);
            $entityManager->flush();

            return $this->redirectToRoute('app_week_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('week/new.html.twig', [
            'week' => $week,
            'form' => $form,
        ]);
    }


   
    #[Route('/{id}/show', name: 'app_week_show', methods: ['GET'])]
    public function show(Week $week, TeamRepository $teamRepository): Response
    {
       
        $teams = $teamRepository->findBy(['league' => $week->getLeague()]);

        return $this->render('week/show.html.twig', [
            'teams' => $teams,
        ]);
    }
    
    

    #[Route('/{id}/edit', name: 'app_week_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Week $week, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WeekType::class, $week);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_week_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('week/edit.html.twig', [
            'week' => $week,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_week_delete', methods: ['POST'])]
    public function delete(Request $request, Week $week, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$week->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($week);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_week_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/choice', name: 'app_week_choice', methods: ['GET', 'POST'])]
    public function makeChoice(Request $request, EntityManagerInterface $entityManager, ChoiceRepository $choiceRepository): Response
    {
        $choice = new Choice();
        $form = $this->createForm(ChoiceType::class, $choice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
            $choice->setUser($this->getUser());

          
            $entityManager->persist($choice);

           
            $entityManager->flush();

            $this->savePlayerChoice($entityManager, $choice);


           
            return $this->redirectToRoute('app_week_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('week/choice.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function savePlayerChoice(EntityManagerInterface $entityManager, Choice $choice): void
    {
        $players = $choice->getPlayers();
        foreach ($players as $player) {
            $player->addChoice($choice);
            $entityManager->persist($player);
        }
        $entityManager->flush();
    }
    
}