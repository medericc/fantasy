<?php

namespace App\Command;

use App\Entity\Team;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-players',
    description: 'Add players from JSON file to the database',
)]
class AddPlayersCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('jsonFile', InputArgument::OPTIONAL, 'Path to the JSON file', '../../teams/lfb/tarbes.json')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $jsonFile = $input->getArgument('jsonFile');

        if ($jsonFile) {
            $io->note(sprintf('You passed a JSON file: %s', $jsonFile));
        }

        if (!file_exists($jsonFile)) {
            $io->error('JSON file not found!');
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($jsonFile);
        $playersData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $io->error('Error decoding JSON: ' . json_last_error_msg());
            return Command::FAILURE;
        }

        foreach ($playersData as $playerData) {
         $team = $this->entityManager->getRepository(Team::class)->findOneBy(['name' => $playerData['team']]);

            if (!$team) {
                $team = new Team();
                $team->setName($playerData['team']);
                $this->entityManager->persist($team);
            }

            $player = new Player();
            $player->setForename($playerData['forename']);
            $player->setName($playerData['name']);
            $player->setRate($playerData['rate']);
            $player->setTeam($team);

            $this->entityManager->persist($player);
        }

        $this->entityManager->flush();

        $io->success('Players have been successfully added to the database.');

        return Command::SUCCESS;
    }
}
