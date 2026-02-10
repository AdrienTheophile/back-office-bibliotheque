<?php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationsRepository::class)]
class Reservations
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $dateResa = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateResa(): ?\DateTime
    {
        return $this->dateResa;
    }

    public function setDateResa(\DateTime $dateResa): static
    {
        $this->dateResa = $dateResa;

        return $this;
    }
}
