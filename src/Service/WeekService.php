<?php

namespace App\Service;

use App\Entity\Week;
use Doctrine\Persistence\ManagerRegistry;

class WeekService
{
    private $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getWeeksData(): array
    {
        $weeksLF2 = $this->doctrine->getRepository(Week::class)->findBy(['league' => '2']);
        $weeksLFB = $this->doctrine->getRepository(Week::class)->findBy(['league' => '1']);

        return [
            'weeksLF2' => $weeksLF2,
            'weeksLFB' => $weeksLFB,
        ];
    }
}
