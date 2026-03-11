<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Entity\Livre;
use App\Entity\Reservations;
use App\Entity\Utilisateur;
use App\Repository\AdherentRepository;
use App\Repository\LivreRepository;
use App\Repository\ReservationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/adherent', name: 'api_adherent_')]
#[IsGranted('ROLE_ADHERENT')]
class AdherentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private AdherentRepository $adherentRepository,
        private ReservationsRepository $reservationsRepository,
        private LivreRepository $livreRepository
    ) {
    }

    /**
     * Récupère le profil de l'adhérent connecté
     */
    #[Route('/profil', name: 'profil', methods: ['GET'])]
    public function getProfil(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json([
                'error' => 'Aucun profil adhérent trouvé pour cet utilisateur'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $adherent->getIdAdh(),
            'dateAdhesion' => $adherent->getDateAdhesion()?->format('Y-m-d'),
            'dateNaissance' => $adherent->getDateNaiss()?->format('Y-m-d'),
            'adressePostale' => $adherent->getAdressePostale(),
            'telephone' => $adherent->getNumTel(),
            'photo' => $adherent->getPhoto(),
            'utilisateur' => [
                'email' => $user->getEmail(),
                'nom' => $user->getNom(),
                'prenom' => $user->getPrenom(),
            ]
        ];

        return $this->json($data);
    }

    /**
     * Récupère les emprunts de l'adhérent connecté
     */
    #[Route('/emprunts', name: 'emprunts', methods: ['GET'])]
    public function getEmprunts(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json([
                'error' => 'Aucun profil adhérent trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $emprunts = $adherent->getEmprunts();
        $data = [];

        foreach ($emprunts as $emprunt) {
            $data[] = [
                'id' => $emprunt->getIdEmp(),
                'dateEmprunt' => $emprunt->getDateEmprunt()?->format('Y-m-d'),
                'dateRetour' => $emprunt->getDateRetour()?->format('Y-m-d'),
                'dateRetourReel' => $emprunt->getDateRetourReel()?->format('Y-m-d'),
                'livre' => [
                    'id' => $emprunt->getLivre()?->getIdLivre(),
                    'titre' => $emprunt->getLivre()?->getTitre()
                ]
            ];
        }

        return $this->json([
            'emprunts' => $data,
            'total' => count($data)
        ]);
    }

    /**
     * Récupère les réservations de l'adhérent connecté
     */
    #[Route('/reservations', name: 'reservations', methods: ['GET'])]
    public function getReservations(): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json([
                'error' => 'Aucun profil adhérent trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $reservations = $adherent->getReservations();
        $data = [];

        foreach ($reservations as $reservation) {
            $data[] = [
                'id' => $reservation->getIdResa(),
                'dateReservation' => $reservation->getDateResa()?->format('Y-m-d'),
                'livre' => [
                    'id' => $reservation->getLivre()?->getIdLivre(),
                    'titre' => $reservation->getLivre()?->getTitre()
                ]
            ];
        }

        return $this->json([
            'reservations' => $data,
            'total' => count($data)
        ]);
    }

    /**
     * Crée une nouvelle réservation
     */
    #[Route('/reservations', name: 'create_reservation', methods: ['POST'])]
    public function createReservation(Request $request): JsonResponse
    {
        // Supprime les réservations expirées (> 7 jours)
        $this->reservationsRepository->deleteExpiredReservations();

        /** @var Utilisateur $user */
        $user = $this->getUser();
        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json([
                'error' => 'Aucun profil adhérent trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['livreId'])) {
            return $this->json([
                'error' => 'L\'ID du livre est requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $livre = $this->livreRepository->find($data['livreId']);
        
        if (!$livre) {
            return $this->json([
                'error' => 'Livre non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si l'adhérent a déjà une réservation pour ce livre
        $existingReservation = $this->reservationsRepository->findOneBy([
            'adherent' => $adherent,
            'livre' => $livre
        ]);

        if ($existingReservation) {
            return $this->json([
                'error' => 'Vous avez déjà réservé ce livre'
            ], Response::HTTP_CONFLICT);
        }

        $reservation = new Reservations();
        $reservation->setAdherent($adherent);
        $reservation->setLivre($livre);
        
        $dateResa = new \DateTime();
        $dateResa->setTime(0, 0, 0);
        $reservation->setDateResa($dateResa);

        $this->entityManager->persist($reservation);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Réservation créée avec succès',
            'reservation' => [
                'id' => $reservation->getIdResa(),
                'dateReservation' => $reservation->getDateResa()?->format('Y-m-d'),
                'livre' => [
                    'id' => $livre->getIdLivre(),
                    'titre' => $livre->getTitre(),
                ]
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Annule une réservation
     */
    #[Route('/reservations/{id}', name: 'cancel_reservation', methods: ['DELETE'])]
    public function cancelReservation(int $id): JsonResponse
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $adherent = $user->getAdherent();

        if (!$adherent) {
            return $this->json([
                'error' => 'Aucun profil adhérent trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $reservation = $this->reservationsRepository->find($id);

        if (!$reservation) {
            return $this->json([
                'error' => 'Réservation non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que la réservation appartient bien à l'adhérent connecté
        if ($reservation->getAdherent()->getIdAdh() !== $adherent->getIdAdh()) {
            return $this->json([
                'error' => 'Vous n\'êtes pas autorisé à annuler cette réservation'
            ], Response::HTTP_FORBIDDEN);
        }

        $this->entityManager->remove($reservation);
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Réservation annulée avec succès'
        ]);
    }
}
