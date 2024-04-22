<?php

namespace App\Controller;

use App\Entity\Week;
use App\Service\WeekService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    private $weekService;

    public function __construct(WeekService $weekService)
    {
        $this->weekService = $weekService;
    }

    #[Route('/dashboard/week/{id}', name: 'app_dashboard_id', methods: ['GET'])]
    public function index($id): Response
    {
        $matchesLFBJson = file_get_contents('../matchlfb.json');
        $matchesLF2Json = file_get_contents('../matchlf2.json');
    
        $matchesLFB = json_decode($matchesLFBJson, true);
        $matchesLF2 = json_decode($matchesLF2Json, true);
    
        $weeksData = $this->weekService->getWeeksData();
        $weeksLF2 = $weeksData['weeksLF2'];
        $weeksLFB = $weeksData['weeksLFB'];
    
        $matchesLF2Filtered = [];
        $matchesLFBFiltered = [];
    
        foreach ($matchesLF2 as $matches) {
            foreach ($matches as $match) {
                if ($match['match_day'] == $id) {
                    $matchesLF2Filtered[] = $match;
                }
            }
        }
    
        foreach ($matchesLFB as $matches) {
            foreach ($matches as $match) {
                if ($match['match_day'] == $id) {
                    $matchesLFBFiltered[] = $match;
                }
            }
        }
    
        return $this->render('dashboard/index.html.twig', [
            'weekId' => $id,
            'matchesLF2' => $matchesLF2Filtered,
            'matchesLFB' => $matchesLFBFiltered,
            'weeksLF2' => $weeksLF2, 
            'weeksLFB' => $weeksLFB, 
        ]);
    }
    

    

    #[Route('/dashboard', name: 'app_dashboard')]
    public function show(): Response
    { 
        $weeksData = $this->weekService->getWeeksData();
        $weeksLF2 = $weeksData['weeksLF2'];
        $weeksLFB = $weeksData['weeksLFB'];
    
        return $this->render('dashboard/show.html.twig', [
            'controller_name' => 'DashboardController',
            'weeksLFB' => $weeksLFB, 
            'weeksLF2' => $weeksLF2, 
        ]);
    }
}
