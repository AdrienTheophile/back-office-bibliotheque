<?php

namespace App\Entity;

use App\Repository\ReservationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ReservationsRepository::class)]
class Reservations
{
    #[Assert\Callback]
    public function validateLimit(ExecutionContextInterface $context): void
    {
        // Seulement si c'est une nouvelle réservation (idResa === null)
        if ($this->adherent && $this->idResa === null) {
            $count = $this->adherent->getReservations()->count();
            if ($count >= 3) {
                $context->buildViolation('Cet adhérent a déjà atteint la limite de 3 réservations simultanées.')
                    ->atPath('adherent')
                    ->addViolation();
            }
        }
    }
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['reservation:read', 'adherent:read'])]
    private ?int $idResa = null;

    #[ORM\Column]
    #[Groups(['reservation:read', 'adherent:read'])]
    private ?\DateTime $dateResa = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_adh')]
    private ?Adherent $adherent = null;

    #[ORM\OneToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false, referencedColumnName: 'id_livre')]
    #[Groups(['reservation:read', 'adherent:read'])]
    private ?Livre $livre = null;

    public function getIdResa(): ?int
    {
        return $this->idResa;
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

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(?Adherent $adherent): static
    {
        $this->adherent = $adherent;

        return $this;
    }

    public function getLivre(): ?Livre
    {
        return $this->livre;
    }

    public function setLivre(Livre $livre): static
    {
        $this->livre = $livre;

        return $this;
    }
}
