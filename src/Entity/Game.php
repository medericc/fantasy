<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?team $team_home = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?team $team_away = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTeamHome(): ?team
    {
        return $this->team_home;
    }

    public function setTeamHome(?team $team_home): static
    {
        $this->team_home = $team_home;

        return $this;
    }

    public function getTeamAway(): ?team
    {
        return $this->team_away;
    }

    public function setTeamAway(team $team_away): static
    {
        $this->team_away = $team_away;

        return $this;
    }
}
