<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use App\Repository\UserRepository;
use App\Repository\ChoiceRepository;
use App\Repository\WeekRepository;
use Symfony\Bundle\SecurityBundle\Security;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $userRepository;
    private $choiceRepository;
    private $weekRepository;
    private $security;

    public function __construct(UserRepository $userRepository, ChoiceRepository $choiceRepository, WeekRepository $weekRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->choiceRepository = $choiceRepository;
        $this->weekRepository = $weekRepository;
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();

        if ($user) {
            // Récupérer les utilisateurs triés par points LFB et LF2
            $usersLFB = $this->userRepository->findBy([], ['ptl_lfb' => 'DESC']);
            $usersLF2 = $this->userRepository->findBy([], ['pt_lf2' => 'DESC']);

            // Calculer les rangs
            $rankLFB = array_search($user, $usersLFB) + 1;
            $rankLF2 = array_search($user, $usersLF2) + 1;

            // Trouver la dernière semaine remplie pour LFB et LF2
            $latestWeekLFB = $this->weekRepository->findLatestFilledWeek(1, 22); // LFB: semaines 1-22
            $latestWeekLF2 = $this->weekRepository->findLatestFilledWeek(23, 44); // LF2: semaines 23-44

            // Obtenir les choix et calculer les points pour les semaines correspondantes
            $choicesLFB = $this->choiceRepository->findBy(['week' => $latestWeekLFB, 'user' => $user]);
            $choicesLF2 = $this->choiceRepository->findBy(['week' => $latestWeekLF2, 'user' => $user]);

            // Calculer les points totaux
            $totalPointsLFB = array_reduce($choicesLFB, fn($carry, $choice) => $carry + $choice->getPoints(), 0);
            $totalPointsLF2 = array_reduce($choicesLF2, fn($carry, $choice) => $carry + $choice->getPoints(), 0);

            return [
                'rankLFB' => $rankLFB,
                'rankLF2' => $rankLF2,
                'totalPointsLFB' => $totalPointsLFB,
                'totalPointsLF2' => $totalPointsLF2,
            ];
        }

        return [];
    }
}
