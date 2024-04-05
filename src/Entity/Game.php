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
    private ?Team $team_home = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team_away = null;

    #[ORM\ManyToOne(inversedBy: 'game_week')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Week $week = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTeamHome(): ?Team
    {
        return $this->team_home;
    }

    public function setTeamHome(?Team $team_home): static
    {
        $this->team_home = $team_home;

        return $this;
    }

    public function getTeamAway(): ?Team
    {
        return $this->team_away;
    }

    public function setTeamAway(Team $team_away): static
    {
        $this->team_away = $team_away;

        return $this;
    }

    public function getWeek(): ?Week
    {
        return $this->week;
    }

    public function setWeek(?Week $week): static
    {
        $this->week = $week;

        return $this;
    }
}
