<?php

namespace App\Controller\Api;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Ce code ne s'exécute jamais : LexikJWT intercepte la requête avant
        return $this->json(['message' => 'Missing credentials'], 401);
    }

    #[Route('/user/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        $adherent = $user->getAdherent();

        return $this->json([
            'id'       => $user->getId(),
            'email'    => $user->getEmail(),
            'nom'      => $user->getNom(),
            'prenom'   => $user->getPrenom(),
            'roles'    => $user->getRoles(),
            'adherent' => $adherent ? [
                'id'             => $adherent->getIdAdh(),
                'dateAdhesion'   => $adherent->getDateAdhesion()?->format('Y-m-d'),
                'numTel'         => $adherent->getNumTel(),
                'adressePostale' => $adherent->getAdressePostale(),
                'estActif'       => $adherent->isEstActif(),
            ] : null,
        ]);
    }

    #[Route('/user/me', name: 'api_user_me_update', methods: ['PATCH'])]
    public function updateMe(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
    ): JsonResponse {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        $data = json_decode($request->getContent(), true);

        if (!empty($data['nom']))      $user->setNom($data['nom']);
        if (!empty($data['prenom']))   $user->setPrenom($data['prenom']);
        if (!empty($data['email']))    $user->setEmail($data['email']);
        if (!empty($data['password'])) {
            $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        }

        $adherent = $user->getAdherent();
        if ($adherent) {
            if (!empty($data['numTel']))         $adherent->setNumTel($data['numTel']);
            if (!empty($data['adressePostale'])) $adherent->setAdressePostale($data['adressePostale']);
        }

        $em->flush();

        return $this->json(['message' => 'Profil mis à jour avec succès']);
    }
}