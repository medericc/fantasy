<?php

namespace App\DataFixtures;

use App\Entity\Player;
use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;


class PlayerFixtures extends Fixture 
{
        
    public function __construct(private TeamRepository $teamRepository){
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        for ($i=0;$i < 24;$i++) {
            $player = new Player();
            $player->setForename($faker->firstName);
            $player->setName($faker->lastName);
            $team = $this->teamRepository->findOneBy(['id'=> $faker->numberBetween(1,24)]);
            $player->setRate(rand(-8,20));
            $player->setTeam($team);

            $manager->persist($player); 
        }
        $manager->flush();
    }
}
