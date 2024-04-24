<?php

namespace App\Controller;

use App\Entity\Week;
use App\Service\WeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(private readonly WeekService $weekService){}

    #[Route('/dashboard/week/{id}', name: 'app_dashboard_id', methods: ['GET'])]
    public function index($id): Response
    {
        $matchesLFB = json_decode(file_get_contents('../matchlfb.json'), true);
        $matchesLF2 = json_decode(file_get_contents('../matchlf2.json'), true);


        $matchesLFBFiltered = []; 
        $matchesLF2Filtered = [];
        $id <=22 ? 
        $matchesLFBFiltered = $matchesLFB[$id] : 
        $matchesLF2Filtered = $matchesLF2[$id];
        
        return $this->render('dashboard/index.html.twig', [
            'weekId' => $id,
            'matchesLF2' => $matchesLF2Filtered,
            'matchesLFB' => $matchesLFBFiltered
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
