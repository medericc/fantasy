<?php

namespace App\DataFixtures;

use App\Entity\Week;
use App\Entity\League; 
use App\Repository\TeamRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;// Import de l'entité League

class WeekFixtures extends Fixture 
{
    private $teamRepository;

    public function __construct(TeamRepository $teamRepository)

    {
        $this->teamRepository = $teamRepository;
    }

    public function load(ObjectManager $manager): void
    {
        $jsonData = file_get_contents('week.json');
        $data = json_decode($jsonData, true);

        foreach ($data as $item) {
            $week = new Week(); 
            $week->setId($item['id']); 
            $week->setName($item['name']);

           
            $league = $manager->getRepository(League::class)->find($item['league']);
            if (!$league) {
                throw new \Exception('League with id ' . $item['league'] . ' not found.');
            }

            $week->setLeague($league);

            $manager->persist($week);
        }

        $manager->flush();
    }
}
